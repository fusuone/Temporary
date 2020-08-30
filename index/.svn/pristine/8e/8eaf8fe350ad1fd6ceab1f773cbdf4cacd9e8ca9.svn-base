import React, { PureComponent, Fragment } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Spin, Table, Card, Input, message, Modal, Form, Select } from 'antd';

import http from '@/utils/http';
import config from '@/common/config';
import styles from './styles.less';

const initialSearchParams = {
  custname: '',
  ispana: '0' // 0 客户，1合作伙伴
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class CustomerList extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '客户名称',
      dataIndex: 'title',
      width: 130
    }, {
      title: '操作',
      key: 'action',
      render: text => (
        <Fragment>
          <a onClick={() => this.gotoEdit(text)}>修改</a>
        </Fragment>
      )
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
      delLoading: false
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
        page: 1,
        custname: fieldsValue.custname,
        ispana: fieldsValue.ispana
      }, this.getList);
    });
  }

  // 刷新
  onRefresh = (type) => {
    if (type === 'reset') {
      this.props.form.resetFields('custname');
      this.changeReqParams({
        page: 1,
        ...initialSearchParams,
        ispana: this.state.reqParams.ispana // 不需要重置
      }, this.getList);
    } else {
      this.getList();
    }
  }

  // 删除
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
          this.gotoAdd();
          this.setState({ delLoading: true });
          http({
            method: 'get',
            api: 'delcustomer',
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
    this.togglePage('add');
  }

  // 去编辑
  gotoEdit = (item) => {
    this.togglePage('edit', item);
  }

  togglePage = (type, item) => {
    const { togglePage } = this.props;
    togglePage && togglePage(type, item);
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
      api: 'getcustomer',
      params: reqParams
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
      form: { getFieldDecorator }
    } = this.props;
    return (
      <div className={styles.tableListSearch}>
        <Form onSubmit={this.onSearch} hideRequiredMark>
          <Row gutter={8}>
            <Col span={12}>
              <Form.Item>
                {getFieldDecorator('ispana', {
                  initialValue: initialSearchParams.ispana
                })(
                  <Select>
                    <Select.Option value="0">普通客户</Select.Option>
                    <Select.Option value="1">合作伙伴</Select.Option>
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item>
                {getFieldDecorator('custname')(
                  <Input placeholder="客户名称" />
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row>
            <Col>
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

  renderOperator() {
    return (
      <Row gutter={8} className={styles.tableListOperator}>
        <Col span={12}>
          <Button icon="plus" type="dashed" block onClick={this.gotoAdd}>
            新增
          </Button>
        </Col>
        <Col span={12}>
          <Button icon="delete" type="dashed" block onClick={this.onDelete}>
            删除
          </Button>
        </Col>
      </Row>
    );
  }

  render() {
    const { listData, reqParams, selectedRowKeys, getLoading, delLoading } = this.state;
    return (
      <div className={styles.tableList}>
        <Spin spinning={getLoading || delLoading}>
          <Card bordered={false}>
            {this.renderSearch()}
            {this.renderOperator()}
            <Table
              scroll={{ y: 350 }}
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
      </div>
    );
  }
}

export default CustomerList;
