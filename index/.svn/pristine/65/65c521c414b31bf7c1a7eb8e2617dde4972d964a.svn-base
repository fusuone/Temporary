/* eslint-disable no-multiple-empty-lines */
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Spin, Table, Icon, Card, Form, message, Modal, Input } from 'antd';
import moment from 'moment';
import http from '@/utils/http';
import config from '@/common/config';
import SelectPointRule from '@/cps/SelectComponents/SelectPointRule';
import SelectRule from '@/cps/SelectComponents/SelectRule';
import styles from './SetOut.less';
import RuleAdd from './RuleAdd';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  ruletype: '',
  staffname: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class Rule extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '人员名称',
      dataIndex: 'staffname',
      width: 130
    }, {
      title: '制单日期',
      dataIndex: 'billdate',
      width: 130
    }, {
      title: '规则名',
      dataIndex: 'rulename',
      width: 130
    }, {
      title: '规则分类',
      dataIndex: 'ruletype',
      width: 130
    }, {
      title: '积分',
      dataIndex: 'points',
      width: 130
    }, {
      title: '规则说明',
      dataIndex: 'remark',
      width: 130
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
      getLoading: false,
      submitting: false,
      showRuleAdd: false,
      showSelectRule: false,
      addOrEdit: null,
      activeItem: {}
    };

    props.getContext && props.getContext(this);
  }

  componentDidMount() {
    this.getList();
  }


  // 搜索
  onSearch = (e) => {
    e.preventDefault();
    const { form } = this.props;
    form.validateFields((err, fieldsValue) => {
      if (err) return;
      this.changeReqParams({
        // begindate: fieldsValue.date[0],
        // enddate: fieldsValue.date[1],
        ruletype: fieldsValue.rule ? fieldsValue.rule[0].rulename : '',
        staffname: fieldsValue.staffname
      }, this.getList);
    });
  }

  // 刷新
  onRefresh = (type) => {
    if (type === 'reset') {
      this.props.form.resetFields();
      this.changeReqParams({
        page: 1,
        ...initialSearchParams
      }, this.getList);
    } else {
      this.getList();
    }
  }

  // 录入码单
  addTrack = () => {
    //
  }

  onDelete = () => {
    const { delLoading, selectedRowKeys } = this.state;
    if (delLoading) return;
    if (selectedRowKeys.length <= 0) {
      message.info('请至少选择一项');
    } else {
      Modal.confirm({
        title: `选中${selectedRowKeys.length}条，确定删除？`,
        okText: '确认',
        okType: 'danger',
        cancelText: '取消',
        onOk: () => {
          this.setState({ delLoading: true });
          http({
            method: 'get',
            api: 'delpointrule',
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
            this.setState({ delLoading: false });
          });
        }
      });
    }
  }

  // 去新增
  gotoAdd = () => {
    this.setState({
      showRuleAdd: true,
      addOrEdit: '0',
      activeItem: {}
    });
  }


  handleTableChange = (pagination) => {
    this.changeReqParams({
      page: pagination.current
    }, this.getList);
  }


  // 选中行
  handleRowSelectChange = (selectedRowKeys) => {
    this.setState({ selectedRowKeys });
  }


  // 清除选中的行
  clearRowSelect = () => {
    this.setState({ selectedRowKeys: [] });
  }

  // 重置搜索表单
 handleSearchReset = () => {
   this.props.form.resetFields();
   this.changeReqParams({
     ...initialSearchParams
   }, this.getList);
 }

 // 修改所需的请求参数
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
      api: 'getpointrulelist',
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



  renderSearch() {
    const {
      form: { getFieldDecorator, setFieldsValue }
    } = this.props;
    return (
      <div className={styles.tableListSearch}>
        <Form onSubmit={this.onSearch} layout="inline" hideRequiredMark>
          <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
            <Col md={8} sm={24}>
              <Form.Item label="规则分类">
                {getFieldDecorator('rule')(
                  <SelectPointRule style={{ width: '100%' }} />
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
    const { listData, reqParams, selectedRowKeys, getLoading, submitting } = this.state;
    return (
      <div className={styles.tableList}>
        <Spin spinning={getLoading || submitting}>
          <Card bordered={false}>
            {this.renderSearch()}
            <Button icon="plus" type="primary" onClick={this.gotoAdd} style={{ marginBottom: 10 }}>
              录入
            </Button>
            {selectedRowKeys.length > 0 &&
              <Button icon="delete" onClick={this.onDelete} style={{ marginLeft: 10 }}>
              删除
              </Button>
            }
            <Table
              scroll={{ x: 1000, y: 350 }}
              rowKey="id"
              size="middle"
              columns={this.columns}
              dataSource={listData.list}
              onChange={this.handleTableChange}
              rowSelection={{
                selectedRowKeys,
                onChange: this.handleRowSelectChange
              }}
              pagination={{
                total: listData.total,
                current: reqParams.page,
                defaultCurrent: 1,
                defaultPageSize: config.defaultPageSize
              }}
            />
          </Card>
        </Spin>
        <RuleAdd
          visible={this.state.showRuleAdd}
          addOrEdit={this.state.addOrEdit}
          activeItem={this.state.activeItem}
          handleRefresh={() => this.onRefresh('reset')}
          handleVisible={bool => this.setState({ showRuleAdd: bool })}
        />
      </div>
    );
  }
}

export default Rule;
