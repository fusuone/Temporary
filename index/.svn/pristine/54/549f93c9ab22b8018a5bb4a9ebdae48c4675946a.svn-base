// 出库订单体明细
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Modal, message, Spin, Table, Button, Input, Form } from 'antd';

import http from '@/utils/http';
import styles from './OrderList.less';

const initialSearchParams = {
  serialno: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class OrderBodyList extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '缸号/布号',
      dataIndex: 'suffixno',
      width: 130
    }, {
      title: '批号',
      dataIndex: 'indexno',
      width: 130
    }, {
      title: '卷号',
      dataIndex: 'batchno',
      width: 130
    }, {
      title: '加工工艺',
      dataIndex: 'jggy',
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
      title: '来胚匹数',
      dataIndex: 'qty',
      width: 80
    }, {
      title: '价格',
      dataIndex: 'price',
      width: 80
    }, {
      title: '长度',
      dataIndex: 'bigness',
      width: 80
    }, {
      title: '单位',
      dataIndex: 'unit',
      width: 80
    }, {
      title: '经向',
      dataIndex: 'vertical',
      width: 80
    }, {
      title: '纬向',
      dataIndex: 'weft',
      width: 80
    }, {
      title: '加减码',
      dataIndex: 'increq',
      width: 80
    }, {
      title: '成品缩率',
      dataIndex: 'cpsl',
      width: 80
    }, {
      title: '成品门幅',
      dataIndex: 'cpmf',
      width: 80
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
        admin: props.currentUser.admin,
        ...initialSearchParams
      },
      selectedRowKeys: [],
      getLoading: false,
      delLoading: false
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
        serialno: fieldsValue.serialno
      }, this.getList);
    });
  }

  // 刷新
  onRefresh = () => {
    this.props.form.resetFields();
    this.changeReqParams({
      ...initialSearchParams
    }, this.getList);
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
            api: 'delsalorderbody',
            params: { items: selectedRowKeys.join(',') }
          }).then((result) => {
            const { status, msg } = result;
            if (status === '0') {
              message.success(msg);
              this.onRefresh();
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

  handleCancel = () => {
    const { handleVisible = () => null } = this.props;
    handleVisible(false);
  }

  // 关闭之后
  handleAfterClose = () => {
    this.setState({
      listData: {
        list: [],
        total: 0
      }
    });
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

  getList = () => {
    if (this.state.getLoading) return;
    this.clearRowSelect();
    this.setState({ getLoading: true });
    http({
      method: 'get',
      api: 'getsalorderbody',
      params: {
        ...this.state.reqParams,
        orderhead_billno: this.props.orderHeadItem.billno
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState(prevState => ({
          getLoading: false,
          listData: {
            ...prevState.listData,
            list: data.list,
            total: Number(data.total)
          }
        }));
      } else {
        message.warn(msg);
        this.setState({ getLoading: false });
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
              <Form.Item label="码单编号">
                {getFieldDecorator('serialno', {
                  initialValue: ''
                })(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col md={16} sm={24}>
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
    return (this.state.selectedRowKeys.length > 0 &&
      <div className={styles.tableListOperator}>
        <Button icon="delete" type="danger" onClick={this.onDelete}>
          删除
        </Button>
      </div>
    );
  }

  render() {
    const { getLoading, delLoading, listData, selectedRowKeys } = this.state;
    const { orderHeadItem } = this.props;
    return (
      <Modal
        title={`订单清单(${orderHeadItem.billno})`}
        width="90%"
        maskClosable={false}
        visible={this.props.visible}
        onCancel={this.handleCancel}
        afterClose={this.handleAfterClose}
        footer={(
          <div style={{ display: 'flex', flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-end' }}>
            <div />
            <div>
              <Button onClick={this.handleCancel}>关闭</Button>
              <Button type="primary" onClick={this.onRefresh}>刷新</Button>
            </div>
          </div>
        )}
      >
        <div className={styles.tableList}>
          <Spin spinning={getLoading || delLoading}>
            {this.renderSearch()}
            {this.renderOperator()}
            <Table
              rowKey="id"
              size="middle"
              scroll={{ x: 1750, y: 350 }}
              bordered={false}
              columns={this.columns}
              dataSource={listData.list}
              pagination={false}
              onRow={record => ({
                onClick: () => {
                  this.handleRowSelectChange([record.id], [record]);
                }
              })}
              rowSelection={{
                selectedRowKeys,
                onChange: this.handleRowSelectChange
              }}
            />
          </Spin>
        </div>
      </Modal>
    );
  }
}

export default OrderBodyList;
