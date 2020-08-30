// 选择细码
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'dva';
import { Row, Col, Modal, message, Spin, Table, Button, Input, Form, DatePicker } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import config from '@/common/config';

import ModalTitle from '../ModalTitle';
import styles from './SelectTrack.less';

const initialSearchParams = {
  begindate: moment().startOf('month'),
  enddate: moment().endOf('month'),
  serialno: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class SelectTrack extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '日期',
      dataIndex: 'billdate',
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

  componentWillReceiveProps(nextProps) {
    if (nextProps.visible && nextProps.visible !== this.props.visible) {
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
        serialno: fieldsValue.serialno
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

  getList = () => {
    const { loading, reqParams } = this.state;
    if (loading) return;
    this.clearRowSelect();
    this.setState({ loading: true });
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
        title={<ModalTitle title="选择细码" onReload={this.onRefresh} onClose={this.handleCancel} />}
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
              scroll={{ x: 1750, y: 350 }}
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

SelectTrack.propTypes = {
  selectType: PropTypes.oneOf(['checkbox', 'radio'])
};

SelectTrack.defaultProps = {
  selectType: 'radio'
};

export default SelectTrack;
