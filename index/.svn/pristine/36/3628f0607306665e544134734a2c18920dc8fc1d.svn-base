import React, { PureComponent } from 'react';
import { connect } from 'dva';
import router from 'umi/router';
import { Row, Col, Button, Spin, Table, Card, Input, Form, message, Modal, DatePicker, Icon } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import config from '@/common/config';

import SelectCustomer from '@/cps/SelectComponents/SelectCustomer';
import styles from './styles.less';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  serialno: '',
  custno: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class TrackList extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '日期',
      dataIndex: 'billdate',
      width: 130
    }, {
      title: '客户',
      dataIndex: 'custname',
      width: 130
    }, {
      title: '单号',
      dataIndex: 'serialno',
      width: 160
    }, {
      title: '批号',
      dataIndex: 'indexno',
      width: 160
    }, {
      title: '卷号',
      dataIndex: 'machineno',
      width: 160
    }, {
      title: '缸号',
      dataIndex: 'suffixno',
      width: 130
    }, {
      title: '规格',
      dataIndex: 'model',
      width: 130
    }, {
      title: '颜色',
      dataIndex: 'color',
      width: 80
    }, {
      title: '卷数',
      dataIndex: 'qty',
      width: 80
    }, {
      title: '单价',
      dataIndex: 'price',
      width: 80
    }, {
      title: '实际长度',
      dataIndex: 'bigness',
      width: 80
    }, {
      title: '录入员',
      dataIndex: 'username',
      width: 130
    }, {
      title: '加减码',
      dataIndex: 'increq',
      width: 80
    }, {
      title: '加工工序',
      dataIndex: 'processtxt',
      width: 130
    }, {
      title: '备注',
      dataIndex: 'remark',
      width: 150
    }, {
      key: '_fill_'
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
      showSelectCustomer: false,
      getLoading: false,
      delLoading: false
    };
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
        serialno: fieldsValue.serialno,
        custno: fieldsValue.custno
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
            api: 'deltrack',
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
    router.push('/textile/track/cut');
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
      api: 'gettracklist',
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
              <Form.Item label="录入日期">
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
                {getFieldDecorator('serialno')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="客户">
                {getFieldDecorator('custname')(
                  <Input
                    readOnly
                    suffix={<Icon type="down" style={{ color: 'rgba(0,0,0,.25)' }} />}
                    onClick={() => this.setState({ showSelectCustomer: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="客户编号" style={{ display: 'none' }}>
                {getFieldDecorator('custno')(
                  <Input />
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
            <Col span="24">
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
    const {
      form: { setFieldsValue }
    } = this.props;
    return (
      <div className={styles.tableList}>
        <Spin spinning={getLoading || delLoading}>
          <Card bordered={false}>
            {this.renderSearch()}
            {this.renderOperator()}
            <Table
              scroll={{ x: 1750, y: 350 }}
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
        <SelectCustomer
          customerType="0"
          visible={this.state.showSelectCustomer}
          handleVisible={bool => this.setState({ showSelectCustomer: bool })}
          handleOk={(item) => {
            setFieldsValue({
              custno: item.billno,
              custname: item.title
            });
          }}
        />
      </div>
    );
  }
}

export default TrackList;
