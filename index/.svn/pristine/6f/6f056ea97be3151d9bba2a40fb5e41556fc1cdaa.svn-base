import React, { PureComponent, Fragment } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Spin, Table, Card, Input, Form, message, Modal, Select } from 'antd';

import http from '@/utils/http';
import config from '@/common/config';
import styles from './Worker.less';

const { workerMaps } = config;

const initialSearchParams = {
  name: '',
  flag: '100'
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class WorkerList extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '姓名',
      dataIndex: 'worker',
      width: 130
    }, {
      title: '工种',
      dataIndex: 'wtype',
      width: 130
    }, {
      title: '联系电话',
      dataIndex: 'phone',
      width: 130
    }, {
      key: '_fill_'
    }, {
      title: '操作',
      key: 'action',
      fixed: 'right',
      width: 60,
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
        name: fieldsValue.name,
        flag: fieldsValue.flag
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
            api: 'delworker',
            params: { bno: selectedRowKeys.join(',') }
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
      api: 'geworker',
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
        <Form onSubmit={this.onSearch} layout="inline" hideRequiredMark>
          <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
            <Col md={8} sm={24}>
              <Form.Item label="工种">
                {getFieldDecorator('flag', {
                  initialValue: initialSearchParams.flag
                })(
                  <Select>
                    {Object.keys(workerMaps).map(v => (
                      <Select.Option value={v}>{workerMaps[v]}</Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="姓名">
                {getFieldDecorator('name', {
                  initialValue: ''
                })(
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

  renderOperator() {
    return (
      <div className={styles.tableListOperator}>
        <Button icon="plus" type="primary" onClick={this.gotoAdd}>
          新增
        </Button>
        <Button icon="delete" type="danger" onClick={this.onDelete}>
          删除
        </Button>
      </div>
    );
  }

  render() {
    const { listData, reqParams, selectedRowKeys, getLoading, delLoading } = this.state;
    return (
      <div className={styles.tableList}>
        <Spin spinning={getLoading || delLoading}>
          <Card title="数据列表" bordered={false}>
            {this.renderSearch()}
            {this.renderOperator()}
            <Table
              scroll={{ x: 1050, y: 350 }}
              rowKey="billno"
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

export default WorkerList;
