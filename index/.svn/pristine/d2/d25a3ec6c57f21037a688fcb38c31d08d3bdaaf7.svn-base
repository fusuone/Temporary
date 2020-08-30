// 选择胚布
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'dva';
import { Row, Col, Modal, message, Spin, Table, Button, Input, Form, DatePicker, Badge } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import config from '@/common/config';

import ModalTitle from '../ModalTitle';
import styles from './SelectCrude.less';

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
class SelectCrude extends PureComponent {
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
        ...initialSearchParams
      },
      // 选择
      selectedRows: [],
      selectedRowKeys: []
    };
  }

  async componentDidMount() {
    if (this.props.isAutoFetchData) {
      await this.getList();
      const { list } = this.state.listData;
      if (list.length > 0) {
        this.props.handleOk([list[0]]);
      }
    }
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
        begindate: fieldsValue.date[0],
        enddate: fieldsValue.date[1],
        custname: fieldsValue.custname
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

  onRowSelect = (record) => {
    if (this.props.selectType === 'radio') {
      this.setState({
        selectedRowKeys: [record.billno],
        selectedRows: [record]
      });
    } else {
      const { selectedRowKeys, selectedRows } = this.state;
      const _selectedRowKeys = [...selectedRowKeys];
      const _selectedRows = [...selectedRows];
      const indedx = _selectedRowKeys.indexOf(record.billno);
      if (indedx >= 0) {
        _selectedRowKeys.splice(indedx, 1);
        _selectedRows.splice(indedx, 1);
      } else {
        _selectedRowKeys.push(record.billno);
        _selectedRows.push(record);
      }
      this.setState({
        selectedRowKeys: _selectedRowKeys,
        selectedRows: _selectedRows
      });
    }
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

  getList = async () => {
    const { loading, reqParams } = this.state;
    if (loading) return;
    this.clearRowSelect();
    this.setState({ loading: true });
    try {
      const { status, msg, data } = await http({
        method: 'get',
        api: 'getcrudelist',
        params: {
          ...reqParams,
          begindate: reqParams.begindate.format('YYYY-MM-DD'),
          enddate: reqParams.enddate.format('YYYY-MM-DD')
        }
      });
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
    } catch (error) {
      this.setState({ loading: false });
    }
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
                  <DatePicker.RangePicker style={{ width: '100%' }} format="YYYY-MM-DD" />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="客户名称">
                {getFieldDecorator('custname')(
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
        title={<ModalTitle title="选择胚布" onReload={this.onRefresh} onClose={this.handleCancel} />}
        width="90%"
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
              rowKey="billno"
              size="middle"
              scroll={{ x: 2300, y: 350 }}
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
                type: this.props.selectType,
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
        </div>
      </Modal>
    );
  }
}

SelectCrude.propTypes = {
  isAutoFetchData: PropTypes.bool,
  selectType: PropTypes.oneOf(['checkbox', 'radio'])
};

SelectCrude.defaultProps = {
  isAutoFetchData: false, // 用于静默获取列表数据(只会触发一次)，不用弹出Modal再触发获取
  selectType: 'radio'
};

export default SelectCrude;
