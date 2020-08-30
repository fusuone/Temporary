import React, { PureComponent, Fragment } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Spin, Table, Card, Input, Form, message, Modal, DatePicker, Divider, Dropdown, Menu, Icon, Badge } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import config from '@/common/config';
import AddPanel from './AddPanel';
import styles from './DataInput.less';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  custname: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class DataInput extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '审核',
      dataIndex: 'audit',
      width: 100,
      render: text => (text === '1' ?
        <Badge status="success" text="已审" />
        :
        <Badge status="default" text="未审" />
      )
    }, {
      title: '单号',
      dataIndex: 'crudeno',
      width: 160
    }, {
      title: '制单日期',
      dataIndex: 'billdate',
      width: 130
    }, {
      title: '客户',
      dataIndex: 'custname',
      width: 130
    }, {
      title: '加工工厂',
      dataIndex: 'factory',
      width: 130
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
      title: '匹数',
      dataIndex: 'qty',
      width: 80
    }, {
      title: '码长',
      dataIndex: 'extent',
      width: 80
    }, {
      title: '成品缩率',
      dataIndex: 'cpsl',
      width: 130
    }, {
      title: '成品拉斜',
      dataIndex: 'stretch',
      width: 130
    }, {
      title: '成品门幅',
      dataIndex: 'cpmf',
      width: 130
    }, {
      title: '加工工序',
      dataIndex: 'jggy',
      width: 130
    }, {
      title: '斜纹',
      dataIndex: 'veins',
      width: 80
    }, {
      title: '加减码',
      dataIndex: 'increq',
      width: 80
    }, {
      title: '加工类型',
      dataIndex: 'processtype',
      width: 130
    }, {
      title: '加工说明',
      dataIndex: 'processremark',
      width: 130
    }, {
      title: '录入员',
      dataIndex: 'username',
      width: 130
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
      width: 120,
      render: text => (
        <Fragment>
          <a onClick={() => this.gotoEdit(text)}>编辑</a>
          <Divider type="vertical" />
          <Dropdown
            overlay={
              <Menu>
                <Menu.Item>
                  <a onClick={() => this.addTrack(text)}>录入码单</a>
                </Menu.Item>
              </Menu>
            }
          >
            <a>更多 <Icon type="down" /></a>
          </Dropdown>
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
      submitting: false,
      showAddPanel: false,
      addOrEdit: null,
      activeItem: {},
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
        begindate: fieldsValue.date[0],
        enddate: fieldsValue.date[1],
        custname: fieldsValue.custname,
        page: 1
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

  // 删除
  onDelete = () => {
    const { submitting, selectedRowKeys } = this.state;
    if (submitting) return;
    if (selectedRowKeys.length <= 0) {
      message.info('请至少选择一项');
    } else {
      Modal.confirm({
        title: `选中${selectedRowKeys.length}条，确定删除？`,
        okText: '确认',
        okType: 'danger',
        cancelText: '取消',
        onOk: () => {
          this.setState({ submitting: true });
          http({
            method: 'get',
            api: 'delcrude',
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
            this.setState({ submitting: false });
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
  gotoAdd = () => {
    this.setState({
      showAddPanel: true,
      addOrEdit: '0',
      activeItem: {}
    });
  }

  // 去编辑
  gotoEdit = (item) => {
    this.setState({
      showAddPanel: true,
      addOrEdit: '1',
      activeItem: item
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
    this.setState({ selectedRowKeys: [], auditFlag: null });
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
      api: 'getcrudelist',
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
              <Form.Item label="制单日期">
                {getFieldDecorator('date', {
                  initialValue: [initialSearchParams.begindate, initialSearchParams.enddate],
                  rules: [{ required: true, message: '请选择日期' }]
                })(
                  <DatePicker.RangePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="客户名称">
                {getFieldDecorator('custname', {
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
    const { selectedRowKeys, auditFlag } = this.state;
    return (
      <div className={styles.tableListOperator}>
        <Button icon="plus" type="primary" onClick={this.gotoAdd}>
          录入
        </Button>
        {selectedRowKeys.length > 0 &&
          <Fragment>
            {auditFlag !== null &&
              <Button icon="pushpin" onClick={this.onAudit}>
                {auditFlag === 'audited' ? '反审' : '审核'}
              </Button>
            }
            <Button icon="delete" onClick={this.onDelete}>
              删除
            </Button>
          </Fragment>
        }
      </div>
    );
  }

  render() {
    const { listData, reqParams, selectedRowKeys, getLoading, submitting, auditFlag } = this.state;
    return (
      <div className={styles.tableList}>
        <Spin spinning={getLoading || submitting}>
          <Card bordered={false}>
            {this.renderSearch()}
            {this.renderOperator()}
            <Table
              scroll={{ x: 2500, y: 350 }}
              rowKey="id"
              size="middle"
              columns={this.columns}
              dataSource={listData.list}
              onChange={this.handleTableChange}
              rowSelection={{
                selectedRowKeys,
                onChange: this.handleRowSelectChange,
                hideDefaultSelections: true,
                fixed: true,
                getCheckboxProps: (record) => {
                  if (auditFlag === 'audited') {
                    return {
                      disabled: record.audit === '0'
                    };
                  }
                  if (auditFlag === 'unaudited') {
                    return {
                      disabled: record.audit === '1'
                    };
                  }
                  return false;
                },
                selections: [{
                  key: 'audited',
                  text: '已审核的',
                  onSelect: () => {
                    const keys = [];
                    listData.list.forEach((v) => {
                      v.audit === '1' && keys.push(v.id);
                    });
                    this.setState({ selectedRowKeys: keys, auditFlag: 'audited' });
                  }
                }, {
                  key: 'unaudited',
                  text: '未审核的',
                  onSelect: () => {
                    const keys = [];
                    listData.list.forEach((v) => {
                      v.audit === '0' && keys.push(v.id);
                    });
                    this.setState({ selectedRowKeys: keys, auditFlag: 'unaudited' });
                  }
                }, {
                  key: 'cancelaudit',
                  text: '取消',
                  onSelect: () => {
                    this.setState({ selectedRowKeys: [], auditFlag: null });
                  }
                }]
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
        <AddPanel
          visible={this.state.showAddPanel}
          addOrEdit={this.state.addOrEdit}
          activeItem={this.state.activeItem}
          handleRefresh={() => this.onRefresh('reset')}
          handleVisible={bool => this.setState({ showAddPanel: bool })}
        />
      </div>
    );
  }
}

export default DataInput;
