// 选择客户
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'dva';
import { Row, Modal, message, Spin, Table, Input } from 'antd';

import http from '@/utils/http';
import config from '@/common/config';

import ModalTitle from '../ModalTitle';

const initialSearchParams = {
  username: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class showSelectRule extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '员工编号',
      dataIndex: 'billno',
      width: 130
    }, {
      title: '姓名',
      dataIndex: 'username',
      width: 150
    }, {
      title: '联系电话',
      dataIndex: 'phone',
      width: 150
    }, {
      title: '部门',
      dataIndex: 'division',
      width: 150
    }];

    this.state = {
      loading: false,
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
      page: 1,
      username: value
    }, this.getList);
  }

  // 刷新
  onRefresh = () => {
    if (this.searchRef) {
      this.searchRef.input.input.value = '';
    }
    this.changeReqParams({
      page: 1,
      ...initialSearchParams
    }, this.getList);
  }

  handleTableChange = (pagination) => {
    this.changeReqParams({
      page: pagination.current
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
      api: 'getteamselectlist',
      params: {
        ...this.state.reqParams,
        ispana: this.props.customerType
      }
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
    const { loading, listData, reqParams, selectedRowKeys } = this.state;
    const { customerType } = this.props;
    const title = customerType === '0' ? '选择人员编号' : '选择部门';
    return (
      <Modal
        title={<ModalTitle title={title} onReload={this.onRefresh} onClose={this.handleCancel} />}
        maskClosable={false}
        closable={false}
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleOk}
        afterClose={this.handleAfterClose}
      >
        <Spin spinning={loading}>
          <Row style={{ marginBottom: '16px' }}>
            <Input.Search ref={ref => this.searchRef = ref} enterButton="搜索" placeholder="人员姓名" onSearch={this.onSearch} />
          </Row>
          <Table
            rowKey="id"
            size="middle"
            scroll={{ x: 330, y: 300 }}
            bordered={false}
            columns={this.columns}
            dataSource={listData.list}
            onChange={this.handleTableChange}
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
            pagination={{
              total: listData.total,
              current: reqParams.page,
              defaultCurrent: 1,
              defaultPageSize: config.defaultPageSize
            }}
          />
        </Spin>
      </Modal>
    );
  }
}

showSelectRule.propTypes = {
  customerType: PropTypes.oneOf(['0', '1']) // 0 客户，1合作伙伴
};

showSelectRule.defaultProps = {
  customerType: '0'
};

export default showSelectRule;
