// 选择车辆
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Modal, message, Spin, Table, Input } from 'antd';

import http from '@/utils/http';
import ModalTitle from '../ModalTitle';

const initialSearchParams = {
  keyword: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class SelectCustomer extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '车牌号',
      dataIndex: 'reveplate',
      width: 130
    }, {
      title: '车型',
      dataIndex: 'revecar',
      width: 130
    }, {
      title: '类型',
      dataIndex: 'cartype',
      width: 130
    }, {
      title: '跟车电话 ',
      dataIndex: 'phone',
      width: 160
    }, {
      key: '_fill_'
    }];

    this.state = {
      loading: false,
      listData: {
        list: [],
        total: 0
      },
      reqParams: {
        uid: props.currentUser.userno,
        admin: props.currentUser.admin,
        ...initialSearchParams
      },
      // 选择
      selectedRows: [],
      selectedRowKeys: []
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.visible && nextProps.visible !== this.props.visible) {
      const { listData } = this.state;
      if (listData.list.length > 0) return; // 如果存在则不重复获取
      this.onRefresh();
    }
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
      ...initialSearchParams
    }, this.getList);
  }

  handleOk = () => {
    const { handleOk = () => null } = this.props;
    const item = this.state.selectedRows[0];
    if (item) {
      handleOk(item);
      this.handleCancel();
    } else {
      message.info('请选择一项！');
    }
  }

  handleCancel = () => {
    const { handleVisible = () => null } = this.props;
    handleVisible(false);
  }

  // 关闭之后
  handleAfterClose = () => {
    //
  }

  onRowSelect = (record) => {
    this.setState({
      selectedRowKeys: [record.id],
      selectedRows: [record]
    });
  }

  // 选中行
  handleRowSelectChange = (selectedRowKeys, selectedRows) => {
    this.setState({ selectedRowKeys, selectedRows });
  }

  // 清除选中的行
  clearRowSelect = () => {
    this.setState({ selectedRowKeys: [], selectedRows: [] });
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

  getList = () => {
    if (this.state.loading) return;
    this.clearRowSelect();
    this.setState({ loading: true });
    http({
      method: 'get',
      api: 'getcarplate',
      params: this.state.reqParams
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState(prevState => ({
          loading: false,
          listData: {
            ...prevState.listData,
            list: data.list,
            total: Number(data.total)
          }
        }));
      } else {
        message.warn(msg);
        this.setState({ loading: false });
      }
    }).catch(() => {
      this.setState({ loading: false });
    });
  }

  render() {
    const { loading, listData, selectedRowKeys } = this.state;
    return (
      <Modal
        title={<ModalTitle title="选择车辆" onReload={this.onRefresh} onClose={this.handleCancel} />}
        maskClosable={false}
        closable={false}
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleOk}
        afterClose={this.handleAfterClose}
      >
        <Spin spinning={loading}>
          <Row style={{ marginBottom: '16px' }}>
            <Input.Search ref={ref => this.searchRef = ref} enterButton="搜索" placeholder="车牌号" onSearch={this.onSearch} />
          </Row>
          <Table
            rowKey="id"
            size="middle"
            scroll={{ x: 650, y: 300 }}
            bordered={false}
            columns={this.columns}
            dataSource={listData.list}
            pagination={false}
            onRow={record => ({
              onClick: () => {
                this.onRowSelect(record);
              }
            })}
            rowSelection={{
              type: 'radio',
              selectedRowKeys,
              onChange: this.handleRowSelectChange
            }}
          />
        </Spin>
      </Modal>
    );
  }
}

export default SelectCustomer;
