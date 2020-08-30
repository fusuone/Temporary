// 出库订单头明细
import React, { PureComponent, Fragment } from 'react';
import { connect } from 'dva';
import router from 'umi/router';
import { Row, Col, Button, Spin, Table, Card, Input, Form, message, Modal, DatePicker } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import config from '@/common/config';

import OrderBodyList from './OrderBodyList';
import styles from './OrderList.less';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  bno: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class OrderList extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '单号',
      dataIndex: 'billno',
      width: 150
    }, {
      title: '日期',
      dataIndex: 'billdate',
      width: 130
    }, {
      title: '开单人',
      dataIndex: 'username',
      width: 130
    }, {
      title: '客户',
      dataIndex: 'custname',
      width: 130
    }, {
      title: '账单方式',
      dataIndex: 'billstatus',
      width: 130
    }, {
      title: '应收款',
      dataIndex: 'pay',
      width: 80
    }, {
      title: '已收款',
      dataIndex: 'apay',
      width: 80
    }, {
      title: '商品出货仓库',
      dataIndex: 'depotname',
      width: 130
    }, {
      title: '出库员',
      dataIndex: 'operator',
      width: 130
    }, {
      title: '送货人',
      dataIndex: 'driver',
      width: 130
    }, {
      title: '送货车牌',
      dataIndex: 'carplate',
      width: 130
    }, {
      title: '送货地址',
      dataIndex: 'destination',
      width: 130
    }, {
      title: '交货日期',
      dataIndex: 'dealdate',
      width: 130
    }, {
      title: '账期',
      dataIndex: 'period',
      width: 130
    }, {
      title: '发票类型',
      dataIndex: 'invtype',
      width: 130
    }, {
      title: '发票编号',
      dataIndex: 'invoice',
      width: 150
    }, {
      title: '备注',
      dataIndex: 'remark',
      width: 150
    }, {
      key: '_fill_'
    }, {
      title: '操作',
      key: 'action',
      fixed: 'right',
      width: 60,
      render: text => (
        <Fragment>
          <a onClick={() => this.gotoDetail(text)}>详细</a>
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
      delLoading: false,
      showOrderBody: false,
      orderHeadItem: {}
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
        begindate: fieldsValue.date[0],
        enddate: fieldsValue.date[1],
        bno: fieldsValue.bno
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
          this.setState({ delLoading: true });
          http({
            method: 'get',
            api: 'delsalorderhead',
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
    router.push('/textile/sale/output');
  }

  // 去详细
  gotoDetail = (item) => {
    this.setState({
      showOrderBody: true,
      orderHeadItem: item
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
      api: 'getsalorderhead',
      params: {
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
      form: { getFieldDecorator }
    } = this.props;
    return (
      <div className={styles.tableListSearch}>
        <Form onSubmit={this.onSearch} layout="inline" hideRequiredMark>
          <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
            <Col md={8} sm={24}>
              <Form.Item label="日期">
                {getFieldDecorator('date', {
                  initialValue: [initialSearchParams.begindate, initialSearchParams.enddate],
                  rules: [{ required: true, message: '请选择日期' }]
                })(
                  <DatePicker.RangePicker style={{ width: '100%' }} format="YYYY-MM-DD" />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="单号">
                {getFieldDecorator('bno', {
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
          录入
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
          <Card bordered={false}>
            {this.renderSearch()}
            {this.renderOperator()}
            <Table
              scroll={{ x: 2350, y: 350 }}
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
        <OrderBodyList
          visible={this.state.showOrderBody}
          orderHeadItem={this.state.orderHeadItem}
          handleVisible={bool => this.setState({ showOrderBody: bool })}
        />
      </div>
    );
  }
}

export default OrderList;
