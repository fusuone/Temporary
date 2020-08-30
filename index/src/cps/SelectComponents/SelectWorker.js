// 选择工作人员
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Modal, message, Spin, Table, Button, Input, Form } from 'antd';

import http from '@/utils/http';
import config from '@/common/config';

import ModalTitle from '../ModalTitle';
import styles from './SelectWorker.less';

const initialSearchParams = {
  name: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class SelectWorker extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '姓名',
      dataIndex: 'worker',
      width: 130
    }, {
      title: '联系电话',
      dataIndex: 'phone',
      width: 130
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
        ispaging: '2',
        page: 1,
        admin: props.currentUser.admin,
        flag: props.workerType,
        ...initialSearchParams
      },
      // 选择
      selectedRows: [],
      selectedRowKeys: []
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.visible && nextProps.visible !== this.props.visible) {
      if (nextProps.workerType !== this.state.reqParams.flag) {
        // 如果不是同种类型则重新加载
        this.setState(prveState => ({
          reqParams: {
            ...prveState.reqParams,
            flag: nextProps.workerType
          }
        }), this.onRefresh);
      } else {
        // 一样
        const { listData } = this.state;
        if (listData.list.length > 0) return; // 如果存在则不重复获取
        this.onRefresh();
      }
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
        name: fieldsValue.name
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
    const items = this.state.selectedRows;
    if (items.length > 0) {
      handleOk(items);
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
      api: 'geworker',
      params: {
        ...this.state.reqParams
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

  renderSearch() {
    const {
      form: { getFieldDecorator }
    } = this.props;
    return (
      <div className={styles.tableListSearch}>
        <Form onSubmit={this.onSearch} layout="inline" hideRequiredMark>
          <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
            <Col md={12} sm={24}>
              <Form.Item label="姓名">
                {getFieldDecorator('name')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col md={12} sm={24}>
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
    const title = config.workerMaps[reqParams.flag] || '';
    return (
      <Modal
        title={<ModalTitle title={`选择${title}`} onReload={this.onRefresh} onClose={this.handleCancel} />}
        maskClosable={false}
        closable={false}
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleOk}
        afterClose={this.handleAfterClose}
      >
        <div className={styles.tableList}>
          <Spin spinning={loading}>
            {this.renderSearch()}
            <Table
              rowKey="billno"
              size="middle"
              scroll={{ y: 350 }}
              bordered={false}
              columns={this.columns}
              dataSource={listData.list}
              onChange={this.handleTableChange}
              onRow={record => ({
                onClick: () => {
                  this.handleRowSelectChange([record.billno], [record]);
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

export default SelectWorker;
