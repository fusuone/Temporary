import React, { PureComponent } from 'react';
import { connect } from 'dva';
import PageHeaderWrapper from '@/components/PageHeaderWrapper';
import { Row, Col, Button, Spin, Table, Card, Form, message, Modal, DatePicker } from 'antd';
import moment from 'moment';
import SelectPointRule from '@/cps/SelectComponents/SelectPointRule';
import http from '@/utils/http';
import config from '@/common/config';
import styles from './SetOut.less';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  ruletype: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class SetOut extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '单号',
      dataIndex: 'staffno',
      width: 150
    }, {
      title: '日期',
      dataIndex: 'billdate',
      width: 180
    }, {
      title: '积分',
      dataIndex: 'points',
      width: 130
    }, {
      title: '当前积分',
      dataIndex: 'points',
      width: 130
    }, {
      title: '规则分类',
      dataIndex: 'ruletype',
      width: 130
    }, {
      title: '备注',
      dataIndex: 'remark',
      width: 180
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
      submitting: false,
      auditFlag: null // 是否在选择审核处理
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
        ruletype: fieldsValue.rule ? fieldsValue.rule[0].rulename : ''
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
            api: 'delpointtrack',
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

  // 审核
  onAudit = () => {
    const { submitting, selectedRowKeys, auditFlag } = this.state;
    if (submitting) return;
    if (selectedRowKeys.length <= 0) {
      message.info('请至少选择一项');
    } else {
      Modal.confirm({
        title: `选中${selectedRowKeys.length}条，进行${auditFlag === 'audited' ? '反审' : '审核'}？`,
        okText: '确认',
        cancelText: '取消',
        onOk: () => {
          this.setState({ submitting: true });
          http({
            method: 'get',
            api: 'checkcrude',
            params: {
              admin: this.props.currentUser.admin,
              billno: selectedRowKeys.join(','),
              audit: auditFlag === 'audited' ? '0' : '1'
            }
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
            this.setState({ submitting: false });
          });
        }
      });
    }
  }

  // 去新增


  // 去编辑


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
    this.setState({ selectedRowKeys: [], auditFlag: null });
  }

  // 重置搜索表单
  handleSearchReset = () => {
    this.props.form.resetFields();
    this.changeReqParams({
      ...initialSearchParams
    }, this.getList);
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
      api: 'getpointtracklist',
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
              <Form.Item label="积分日期">
                {getFieldDecorator('date', {
                  initialValue: [initialSearchParams.begindate, initialSearchParams.enddate],
                  rules: [{ required: true, message: '请选择日期' }]
                })(
                  <DatePicker.RangePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="规则分类">
                {getFieldDecorator('rule')(
                  <SelectPointRule />
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
    const { listData, reqParams, getLoading, submitting } = this.state;
    return (
      <PageHeaderWrapper>
        <div className={styles.tableList}>
          <Spin spinning={getLoading || submitting}>
            <Card bordered={false}>
              {this.renderSearch()}

              <Table
                scroll={{ x: 1000, y: 350 }}
                rowKey="id"
                size="middle"
                columns={this.columns}
                dataSource={listData.list}
                onChange={this.handleTableChange}

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
      </PageHeaderWrapper>
    );
  }
}

export default SetOut;
