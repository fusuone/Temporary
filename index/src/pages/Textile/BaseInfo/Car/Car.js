// 注：listData.list.map 是为了数据不可变

import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Spin, Table, Card, Input, Divider, message, Popconfirm } from 'antd';

import http from '@/utils/http';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class Car extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '车牌号',
      dataIndex: 'reveplate',
      render: (text, record) => {
        if (record.editable) {
          return (
            <Input
              value={text}
              autoFocus
              onChange={e => this.handleFieldChange(e, 'reveplate', record.id)}
              onKeyPress={e => this.handleKeyPress(e, record.id)}
              placeholder="车牌号"
            />
          );
        }
        return text;
      }
    }, {
      title: '车型',
      dataIndex: 'revecar',
      render: (text, record) => {
        if (record.editable) {
          return (
            <Input
              value={text}
              onChange={e => this.handleFieldChange(e, 'revecar', record.id)}
              onKeyPress={e => this.handleKeyPress(e, record.id)}
              placeholder="车型"
            />
          );
        }
        return text;
      }
    }, {
      title: '类型',
      dataIndex: 'cartype',
      render: (text, record) => {
        if (record.editable) {
          return (
            <Input
              value={text}
              onChange={e => this.handleFieldChange(e, 'cartype', record.id)}
              onKeyPress={e => this.handleKeyPress(e, record.id)}
              placeholder="类型"
            />
          );
        }
        return text;
      }
    }, {
      title: '随车电话',
      dataIndex: 'phone',
      render: (text, record) => {
        if (record.editable) {
          return (
            <Input
              value={text}
              onChange={e => this.handleFieldChange(e, 'phone', record.id)}
              onKeyPress={e => this.handleKeyPress(e, record.id)}
              placeholder="随车电话"
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
        list: []
      },
      reqParams: {
        uid: props.currentUser.userno,
        admin: props.currentUser.admin,
        keyword: ''
      },
      submitting: false,
      getLoading: false,
      delLoading: false
    };

    this.index = 0;
    this.isNewRow = false;
    this.cacheOriginData = {};
  }

  componentDidMount() {
    this.getList();
  }

  // 搜索
  onSearch = (value) => {
    this.changeReqParams({
      keyword: value
    }, this.getList);
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
    const { listData } = this.state;
    if (this.isNewRow) {
      message.info('有一行待添加');
      return;
    }
    const newData = listData.list.map(item => ({ ...item }));
    newData.push({
      id: `NEW_TEMP_ID_${this.index}`,
      reveplate: '',
      revecar: '',
      cartype: '',
      phone: '',
      editable: true,
      isNew: true
    });
    this.index += 1;
    this.isNewRow = true;
    this.setState({
      listData: {
        ...listData,
        list: newData
      }
    });
  }

  // 切换修改
  toggleEditable = (id) => {
    const { listData } = this.state;
    const newData = listData.list.map(item => ({ ...item }));
    const target = newData.filter(item => item.id === id)[0];
    if (!target) return;
    // 进入编辑状态时保存原始数据
    if (!target.editable) {
      this.cacheOriginData[id] = { ...target };
    } else {
      delete this.cacheOriginData[id];
    }
    target.editable = !target.editable;
    this.setState({
      listData: {
        ...listData,
        list: newData
      }
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
  onDelete = (id) => {
    const { listData, delLoading } = this.state;
    if (delLoading) return;
    this.setState({ delLoading: true });
    http({
      method: 'get',
      api: 'delcarplate',
      params: { id }
    }).then((result) => {
      const { status, msg } = result;
      if (status === '0') {
        message.success(msg);
        const newData = listData.list.filter(item => item.id !== id);
        this.setState({
          delLoading: false,
          listData: {
            ...listData,
            list: newData
          }
        });
      } else {
        message.warn(msg);
        this.setState({ delLoading: false });
      }
    }).catch(() => {
      this.setState({ delLoading: false });
    });
  }

  // 提交
  onSave = (id) => {
    const { currentUser } = this.props;
    const { listData, submitting } = this.state;
    if (submitting) return;
    const target = listData.list.filter(item => item.id === id)[0];
    if (!target.reveplate) {
      message.error('请填写车牌号。');
      return;
    }
    this.setState({ submitting: true });
    const data = {
      id: target.isNew ? '' : target.id,
      uid: currentUser.userno,
      admin: currentUser.admin,
      reveplate: target.reveplate,
      revecar: target.revecar,
      cartype: target.cartype,
      phone: target.phone
    };
    http({
      method: 'post',
      api: 'setcarplate',
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
    this.setState({ getLoading: true });
    // 重置
    this.index = 0;
    this.isNewRow = false;
    this.cacheOriginData = {};
    http({
      method: 'get',
      api: 'getcarplate',
      params: reqParams
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          listData: {
            list: data.list
          },
          getLoading: false
        });
      } else {
        message.warn(msg);
        this.setState({
          listData: {
            list: []
          },
          getLoading: false
        });
      }
    }).catch(() => {
      this.setState({ getLoading: false });
    });
  }

  // 按下回车键保存
  handleKeyPress(e, id) {
    if (e.key === 'Enter') {
      this.onSave(id);
    }
  }

  // 处理 row 中的表单输入
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

  render() {
    const { listData, reqParams, getLoading, delLoading, submitting } = this.state;
    return (
      <div>
        <Spin spinning={getLoading || delLoading || submitting}>
          <Card bordered={false}>
            <Row>
              <Col md={8} sm={24} style={{ marginBottom: '16px' }}>
                <Input.Search ref={ref => this.searchRef = ref} enterButton="搜索" placeholder="车牌号" defaultValue={reqParams.keyword} onSearch={this.onSearch} />
              </Col>
            </Row>
            <Table
              rowKey="id"
              size="middle"
              columns={this.columns}
              dataSource={listData.list}
              pagination={false}
            />
            <Button
              style={{ width: '100%', marginTop: 16, marginBottom: 8 }}
              type="dashed"
              onClick={this.onAddNewRow}
              icon="plus"
            >
              新增记录
            </Button>
          </Card>
        </Spin>
      </div>
    );
  }
}

export default Car;
