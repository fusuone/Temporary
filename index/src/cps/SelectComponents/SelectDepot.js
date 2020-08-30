// 选择仓库
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Modal, message, Spin, Table, Button, Input, Form } from 'antd';

import http from '@/utils/http';
import config from '@/common/config';

import ModalTitle from '../ModalTitle';
import styles from './SelectModel.less';

const initialSearchParams = {
  depotname: '',
  depotcode: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class SelectModel extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '编号',
      dataIndex: 'depotcode',
      width: 130
    }, {
      title: '名称',
      dataIndex: 'depotname',
      width: 130
    }, {
      title: '联系人',
      dataIndex: 'linkman',
      width: 80
    }, {
      title: '联系电话',
      dataIndex: 'linkphone',
      width: 130
    }, {
      title: '地址',
      dataIndex: 'address',
      width: 200
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
        page: 1,
        admin: props.currentUser.admin,
        ispaging: '2', // 1开启 2关闭
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
  onSearch = (e) => {
    e.preventDefault();
    const { form } = this.props;
    form.validateFields((err, fieldsValue) => {
      if (err) return;
      this.changeReqParams({
        page: 1,
        depotname: fieldsValue.depotname,
        depotcode: fieldsValue.depotcode
      }, this.getList);
    });
  }

  // 刷新
  onRefresh = () => {
    this.props.form.resetFields();
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

  // 选中行
  handleRowSelectChange = (selectedRowKeys, selectedRows) => {
    this.setState({ selectedRowKeys, selectedRows });
  }

  // 清除选中的行
  clearRowSelect = () => {
    this.setState({ selectedRowKeys: [], selectedRows: [] });
  }

  // 重置搜索表单
  handleSearchReset = () => {
    this.props.form.resetFields();
    this.changeReqParams({
      ...initialSearchParams
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

  getList = () => {
    if (this.state.loading) return;
    this.clearRowSelect();
    this.setState({ loading: true });
    http({
      method: 'get',
      api: 'getdepot',
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

  renderSearch() {
    const {
      form: { getFieldDecorator }
    } = this.props;
    return (
      <div className={styles.tableListSearch}>
        <Form onSubmit={this.onSearch} layout="inline" hideRequiredMark>
          <Row gutter={{ md: 8 }}>
            <Col md={8} sm={24}>
              <Form.Item label="仓库编号">
                {getFieldDecorator('depotcode')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="仓库名称">
                {getFieldDecorator('depotname')(
                  <Input />
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
      </div>
    );
  }

  render() {
    const { loading, listData, reqParams, selectedRowKeys } = this.state;
    return (
      <Modal
        title={<ModalTitle title="选择仓库" onReload={this.onRefresh} onClose={this.handleCancel} />}
        width="50%"
        closable={false}
        maskClosable={false}
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleOk}
        afterClose={this.handleAfterClose}
      >
        <div className={styles.tableList}>
          <Spin spinning={loading}>
            {this.renderSearch()}
            <Table
              rowKey="id"
              size="middle"
              scroll={{ x: 650, y: 350 }}
              bordered={false}
              columns={this.columns}
              dataSource={listData.list}
              onChange={this.handleTableChange}
              onRow={record => ({
                onClick: () => {
                  this.handleRowSelectChange([record.id], [record]);
                }
              })}
              rowSelection={{
                type: 'radio',
                selectedRowKeys,
                onChange: this.handleRowSelectChange
              }}
              pagination={reqParams.ispaging === '1' ? {
                total: listData.total,
                current: reqParams.page,
                defaultCurrent: 1,
                defaultPageSize: config.defaultPageSize
              } : false}
            />
          </Spin>
        </div>
      </Modal>
    );
  }
}

export default SelectModel;
