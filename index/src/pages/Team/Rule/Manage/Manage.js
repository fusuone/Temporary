// 注：listData.list.map 是为了数据不可变

import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Spin, Modal, Table, Icon, DatePicker, Form, Card, Input, Divider, message, Popconfirm } from 'antd';
import moment from 'moment';
import styles from './SetOut.less';
import http from '@/utils/http';
import SelectRule from '@/cps/SelectComponents/SelectRule';
import SelectPointRule from '@/cps/SelectComponents/SelectPointRule';
import ManageAdd from './ManageAdd';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  staffname: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class Manage extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      listData: {
        list: [],
        total: 0
      },
      reqParams: {
        page: 1,
        uid: props.currentUser.userno,
        admin: props.currentUser.admin,
        ...initialSearchParams
      },
      selectedRowKeys: [],
      getLoading: false,
      submitting: false,
      showManageAdd: false,
      showSelectRule: false,
      addOrEdit: null,
      activeItem: {}
    };

    props.getContext && props.getContext(this);
    this.columns = [{
      title: '人员编号',
      dataIndex: 'staffno',
      render: (text, record) => {
        if (record.editable) {
          return (
            <Input
              value={text}
              autoFocus
              onChange={e => this.handleFieldChange(e, 'staffno', record.id)}
              onKeyPress={e => this.handleKeyPress(e, record.id)}
              placeholder="积分编号"
            />
          );
        }
        return text;
      }
    }, {
      title: '人员名称',
      dataIndex: 'staffname'
    }, {
      title: '规则分类',
      dataIndex: 'ruletype',
      render: (text, record) => {
        if (record.editable) {
          return (
            <SelectPointRule
              style={{ width: '100%' }}
              placeholder="规则分类"

            />
          );
        }
        return text;
      }
    }, {
      title: '当前积分',
      dataIndex: 'points',
      render: (text, record) => {
        if (record.editable) {
          return (
            <Input
              value={text}
              onChange={e => this.handleFieldChange(e, 'points', record.id)}
              onKeyPress={e => this.handleKeyPress(e, record.id)}
              placeholder="类型"
            />
          );
        }
        return text;
      }
    }, {
      title: '规则说明',
      dataIndex: 'remark',
      render: (text, record) => {
        if (record.editable) {
          return (
            <Input
              value={text}
              onChange={e => this.handleFieldChange(e, 'remark', record.id)}
              onKeyPress={e => this.handleKeyPress(e, record.id)}
              placeholder="规则说明"
            />
          );
        }
        return text;
      }
    }, {
      title: '操作',
      key: 'action',
      render: (text, record) => {
        const { submitting } = this.state;
        if (!!record.editable && submitting) {
          return null;
        }
        if (record.editable) {
          if (record.isNew) {
            return (
              <span>
                <a onClick={() => this.onSave(record.id)}>添加</a>
                <Divider type="vertical" />
                <a onClick={() => this.onCancel(record.id)}>取消</a>
              </span>
            );
          }
          return (
            <span>
              <a onClick={() => this.onSave(record.id)}>保存</a>
              <Divider type="vertical" />
              <a onClick={() => this.onCancel(record.id)}>取消</a>
            </span>
          );
        }
        return (
          <span>
            <a onClick={() => this.toggleEditable(record.id)}>修改</a>
            <Divider type="vertical" />
            <Popconfirm title="是否要删除此行？" onConfirm={() => this.onDelete(record.id)}>
              <a>删除</a>
            </Popconfirm>
          </span>
        );
      }
    }];

    this.state = {
      listData: {
        list: [],
        total: 0
      },
      reqParams: {
        page: 1,
        uid: props.currentUser.userno,
        admin: props.currentUser.admin,
        ...initialSearchParams
      },
      selectedRowKeys: [],
      submitting: false,
      getLoading: false,
      delLoading: false,
      showSelectRule: false
    };

    this.index = 0;
    this.isNewRow = false;
    this.cacheOriginData = {};
  }

  componentDidMount() {
    this.getList();
  }

// 清除选中的行
clearRowSelect = () => {
  this.setState({ selectedRowKeys: [] });
}

  // 搜索
  onSearch = (value) => {
    value.preventDefault();
    const { form } = this.props;
    form.validateFields((err, fieldsValue) => {
      if (err) return;
      this.changeReqParams({
        page: 1,
        begindate: fieldsValue.date[0],
        enddate: fieldsValue.date[1],
        staffname: fieldsValue.staffname

      }, this.getList);
    });
  }

  // 刷新
  onRefresh = () => {
    if (this.searchRef) {
      this.searchRef.input.input.value = '';
    }
    this.changeReqParams({
      keyword: ''
    }, this.getList);
  }

  // 添加新行
  onAddNewRow = () => {
    this.setState({
      showManageAdd: true,
      addOrEdit: '0',
      activeItem: {}
    });
  }

  // 切换修改
  toggleEditable = (item) => {
    this.setState({
      showManageAdd: true,
      addOrEdit: '1',
      activeItem: item
    });
  }

  // 取消
  onCancel = (id) => {
    const { listData } = this.state;
    let newData = listData.list.map(item => ({ ...item }));
    const target = newData.filter(item => item.id === id)[0];
    if (target.isNew) {
      this.isNewRow = false;
      newData = newData.filter(item => item.id !== id);
    } else {
      if (this.cacheOriginData[id]) {
        Object.assign(target, this.cacheOriginData[id]); // 恢复为原始值
        delete this.cacheOriginData[id];
      }
      target.editable = false;
    }
    this.setState({
      listData: {
        ...listData,
        list: newData
      }
    });
  }

  // 删除
  onDelete = () => {
    const { submitting, selectedRowKeys } = this.state;
    if (submitting) return;
    if (selectedRowKeys.length <= 0) {
      message.info('请至少选择一项');
    } else {
      Modal.confirm({
        title: `选中${selectedRowKeys.length}条，确定删除？`,
        okText: '确认',
        okType: 'danger',
        cancelText: '取消',
        onOk: () => {
          this.setState({ submitting: true });
          http({
            method: 'get',
            api: 'delpointtrack',
            params: { items: selectedRowKeys.join(',') }
          }).then((result) => {
            const { status, msg } = result;
            if (status === '0') {
              message.success(msg);
              this.onRefresh('reset');
            } else {
              message.warn(msg);
            }
          }).catch(() => {
            //
          }).then(() => {
            this.setState({ submitting: false });
          });
        }
      });
    }
  }

  // 提交
  onSave = (id) => {
    const { currentUser } = this.props;
    const { listData, submitting } = this.state;
    if (submitting) return;
    const target = listData.list.filter(item => item.id === id)[0];
    if (!target.staffno) {
      message.error('请填写积分编号。');
      return;
    }
    this.setState({ submitting: true });
    const data = {
      id: target.isNew ? '' : target.id,
      uid: currentUser.userno,
      admin: currentUser.admin,
      staffno: target.staffno,
      staffname: target.staffname,
      rulename: target.rulename,
      ruletype: target.ruletype,
      points: target.points,
      authname: target.authname,
      remark: target.remark
    };
    http({
      method: 'post',
      api: 'setpointtrack',
      data
    }).then(({ status, msg }) => {
      if (status === '0') {
        message.success(msg);
        if (target.isNew) {
          this.onRefresh();
        } else {
          // 取消编辑状态
          this.toggleEditable(target.id);
        }
      } else {
        message.warn(msg);
      }
    }).catch(() => {
      //
    }).then(() => {
      this.setState({ submitting: false });
    });
  }

  // 修改所需的请求参数
  changeReqParams = (params, cb = () => null) => {
    this.setState(prveState => ({
      reqParams: {
        ...prveState.reqParams,
        ...params
      }
    }), cb);
  }

   // 获取数据
   getList = () => {
     const { getLoading, reqParams } = this.state;
     if (getLoading) return;
     this.clearRowSelect();
     this.setState({ getLoading: true });
     http({
       method: 'get',
       api: 'getpointtracklist',
       params:
      {
        ...reqParams,
        begindate: reqParams.begindate.format('YYYY-MM-DD'),
        enddate: reqParams.enddate.format('YYYY-MM-DD')
      }

     }).then((result) => {
       const { status, msg, data } = result;
       if (status === '0') {
         this.setState({
           listData: {
             list: data.list,
             total: Number(data.total)
           },
           getLoading: false
         });
       } else {
         message.warn(msg);
         this.setState({
           listData: {
             list: [],
             total: 0
           },
           getLoading: false
         });
       }
     }).catch(() => {
       this.setState({ getLoading: false });
     });
   }


   // 处理 row 中的表单输入


  handleTableChange = (pagination) => {
    this.changeReqParams({
      page: pagination.current
    }, this.getList);
  }

  // 选中行
  handleRowSelectChange = (selectedRowKeys) => {
    this.setState({ selectedRowKeys });
  }

// 重置搜索表单
handleSearchReset = () => {
  this.props.form.resetFields();
  this.changeReqParams({
    ...initialSearchParams
  }, this.getList);
}

// 按下回车键保存
handleKeyPress(e, id) {
  if (e.key === 'Enter') {
    this.onSave(id);
  }
}

handleFieldChange(e, fieldName, id) {
  const { listData } = this.state;
  const newData = listData.list.map(item => ({ ...item }));
  const target = newData.filter(item => item.id === id)[0];
  if (target) {
    target[fieldName] = e.target.value;
    this.setState({
      listData: {
        ...listData,
        list: newData
      }
    });
  }
}


renderSearch() {
  const {
    form: { getFieldDecorator, setFieldsValue }
  } = this.props;
  return (
    <div className={styles.tableListSearch}>
      <Form onSubmit={this.onSearch} layout="inline" hideRequiredMark>
        <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
          <Col md={8} sm={24}>
            <Form.Item label="积分日期">
              {getFieldDecorator('date', {
                initialValue: [initialSearchParams.begindate, initialSearchParams.enddate],
                rules: [{ required: true, message: '请选择日期' }]
              })(
                <DatePicker.RangePicker style={{ width: '100%' }} />
              )}
            </Form.Item>
          </Col>
          <Col md={8} sm={24}>
            <Form.Item label="人员名称">
              {getFieldDecorator('staffname')(
                <Input
                  readOnly
                  placeholder="请选择"
                  prefix={<Icon type="user" theme="outlined" />}
                  onClick={() => this.setState({ showSelectRule: true })}
                />
              )}
            </Form.Item>
          </Col>
          <Col md={8} sm={24}>
            <span className={styles.submitButtons}>
              <Button type="primary" htmlType="submit">
                  查询
              </Button>
              <Button style={{ marginLeft: '8px' }} onClick={this.handleSearchReset}>
                  重置
              </Button>
            </span>
          </Col>
        </Row>
      </Form>
      <SelectRule
        visible={this.state.showSelectRule}
        handleVisible={bool => this.setState({ showSelectRule: bool })}
        handleOk={(item) => {
          setFieldsValue({
            staffname: item.username
          });
        }}
      />
    </div>
  );
}

render() {
  const { listData, getLoading, delLoading, submitting, selectedRowKeys } = this.state;
  return (
    <div className={styles.tableList}>
      <Spin spinning={getLoading || delLoading || submitting}>

        <Card bordered={false}>
          {this.renderSearch()}
          <Button
            style={{ width: '50%', marginTop: 16, marginBottom: 15 }}
            type="dashed"
            onClick={this.onAddNewRow}
            icon="plus"
          >
              新增记录
          </Button>
          {selectedRowKeys.length > 0 &&
          <Button
            style={{ width: '50%', marginTop: 16, marginBottom: 8 }}
            type="dashed"
            onClick={this.onDelete}
            icon="delete"
          >
              删除
          </Button>
            }
          <Table
            rowKey="id"
            size="middle"
            columns={this.columns}
            dataSource={listData.list}
            onChange={this.handleTableChange}
            rowSelection={{
              selectedRowKeys,
              onChange: this.handleRowSelectChange
            }}
            pagination={false}
          />

        </Card>
      </Spin>
      <ManageAdd
        visible={this.state.showManageAdd}
        addOrEdit={this.state.addOrEdit}
        activeItem={this.state.activeItem}
        handleRefresh={() => this.onRefresh('reset')}
        handleVisible={bool => this.setState({ showManageAdd: bool })}
      />
    </div>
  );
}
}

export default Manage;
