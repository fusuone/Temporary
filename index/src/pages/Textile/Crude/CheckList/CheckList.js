// 胚布检查明细
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Spin, Table, Card, Input, Form, message, Modal, Select, DatePicker } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import config from '@/common/config';
import styles from './CheckList.less';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  custname: '',
  flag: '1'
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class CheckList extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '制单日期',
      dataIndex: 'billdate',
      width: 130
    }, {
      title: '制单编号',
      dataIndex: 'crudeno',
      width: 130
    }, {
      title: '客户名称',
      dataIndex: 'custname',
      width: 130
    }, {
      title: '加工厂',
      dataIndex: 'factory',
      width: 130
    }, {
      title: '规格',
      dataIndex: 'model',
      width: 100
    }, {
      title: '颜色',
      dataIndex: 'color',
      width: 100
    }, {
      title: '来胚匹数',
      dataIndex: 'qty',
      width: 100
    }, {
      title: '来胚码长',
      dataIndex: 'extent',
      width: 100
    }, {
      title: '成品缩率',
      dataIndex: 'cpsl',
      width: 100
    }, {
      title: '成品拉斜',
      dataIndex: 'stretch',
      width: 100
    }, {
      title: '成品门幅',
      dataIndex: 'cpmf',
      width: 100
    }, {
      title: '斜纹',
      dataIndex: 'veins',
      width: 100
    }, {
      title: '加工工序',
      dataIndex: 'processtxt',
      width: 110
    }, {
      title: '加工类型',
      dataIndex: 'processremark',
      width: 110
    }, {
      title: '加工说明',
      dataIndex: 'processremark',
      width: 150
    }, {
      title: '收胚车型',
      dataIndex: 'revecar',
      width: 130
    }, {
      title: '收胚车牌号',
      dataIndex: 'reveplate',
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
        begindate: fieldsValue.date[0],
        enddate: fieldsValue.date[1],
        custname: fieldsValue.custname,
        flag: fieldsValue.flag,
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
            api: 'delxx',
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
      api: reqParams.flag === '1' ? 'getcrudechecklist' : 'getcrudenochecklist',
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
              <Form.Item label="进胚日期">
                {getFieldDecorator('date', {
                  initialValue: [initialSearchParams.begindate, initialSearchParams.enddate],
                  rules: [{ required: true, message: '请选择日期' }]
                })(
                  <DatePicker.RangePicker style={{ width: '100%' }} format="YYYY-MM-DD" />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="检查状态">
                {getFieldDecorator('flag', {
                  initialValue: initialSearchParams.flag
                })(
                  <Select>
                    <Select.Option value="0">未检胚布</Select.Option>
                    <Select.Option value="1">已检胚布</Select.Option>
                  </Select>
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
          新增
        </Button>
        <Button icon="delete" type="danger" onClick={this.onDelete}>
          删除
        </Button>
      </div>
    );
  }

  render() {
    const { listData, reqParams, getLoading, delLoading } = this.state;
    return (
      <div className={styles.tableList}>
        <Spin spinning={getLoading || delLoading}>
          <Card bordered={false}>
            {this.renderSearch()}
            {/* {this.renderOperator()} */}
            <Table
              scroll={{ x: 2000, y: 350 }}
              rowKey="id"
              size="middle"
              columns={this.columns}
              dataSource={listData.list}
              onChange={this.handleTableChange}
              // rowSelection={{
              //   selectedRowKeys,
              //   onChange: this.handleRowSelectChange
              // }}
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

export default CheckList;
