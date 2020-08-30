import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Button, Spin, Table, Card, Form, message, Modal } from 'antd';

import http from '@/utils/http';

import DataInput from './DataInput';
import styles from './styles.less';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class TableList extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '日期',
      dataIndex: 'billdate',
      width: 130
    }, {
      title: '批号',
      dataIndex: 'batchno',
      width: 130
    }, {
      title: '匹号',
      dataIndex: 'indexno',
      width: 130
    }, {
      title: '匹数',
      dataIndex: 'qty',
      width: 80
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
      title: '实际长度',
      dataIndex: 'bigness',
      width: 80
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
      submitting: false,
      listData: [],
      selectedRowKeys: [],
      showDataInput: false
    };

    props.getContext && props.getContext(this);
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
            api: 'deltrack',
            params: { bno: selectedRowKeys.join(',') }
          }).then((result) => {
            const { status, msg } = result;
            if (status === '0') {
              message.success(msg);
              this.getList();
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
    const { getCurrentCrudeItem } = this.props;
    const currentCrudeItem = getCurrentCrudeItem() || {};
    if (Object.keys(currentCrudeItem).length <= 0) {
      message.info('请先选择胚布');
      return;
    }
    this.setState({ showDataInput: true });
  }

  // 选中行
  handleRowSelectChange = (selectedRowKeys) => {
    this.setState({ selectedRowKeys });
  }

  // 清除选中的行
  clearRowSelect = () => {
    this.setState({ selectedRowKeys: [] });
  }

  getList = () => {
    const { loading } = this.state;
    const { currentUser, getCurrentCrudeItem } = this.props;
    const currentCrudeItem = getCurrentCrudeItem() || {};
    if (loading) return;
    if (Object.keys(currentCrudeItem).length <= 0) {
      message.info('请先选择胚布');
      return;
    }
    this.clearRowSelect();
    this.setState({ loading: true });
    http({
      method: 'get',
      api: 'gettracklist',
      params: {
        admin: currentUser.admin,
        serialno: currentCrudeItem.crudeno,
        ispaging: '2'
      }
    }).then((result) => {
      const { status, msg, data } = result;
      if (status === '0') {
        this.setState({
          loading: false,
          listData: data.list
        });
      } else {
        message.warn(msg);
        this.setState({
          loading: false,
          listData: []
        });
      }
    }).catch(() => {
      this.setState({
        loading: false,
        listData: []
      });
    });
  }

  renderOperator() {
    return (
      <div className={styles.tableListOperator}>
        <Button icon="plus" type="primary" onClick={this.gotoAdd}>
          新增
        </Button>
        <Button icon="delete" onClick={this.onDelete}>
          删除
        </Button>
      </div>
    );
  }

  render() {
    const { listData, selectedRowKeys, loading, submitting } = this.state;
    return (
      <div className={styles.tableList}>
        <Spin spinning={loading || submitting}>
          <Card title="细码列表" bordered={false}>
            {this.renderOperator()}
            <Table
              scroll={{ x: 1350 }}
              rowKey="billno"
              size="middle"
              columns={this.columns}
              dataSource={listData}
              pagination={false}
              rowSelection={{
                selectedRowKeys,
                onChange: this.handleRowSelectChange
              }}
            />
          </Card>
        </Spin>
        <DataInput
          visible={this.state.showDataInput}
          activeItem={this.props.getCurrentCrudeItem() || {}}
          handleRefresh={() => this.getList()}
          handleVisible={bool => this.setState({ showDataInput: bool })}
        />
      </div>
    );
  }
}

export default TableList;
