// 选择的出库细码
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Button, Spin, Table, Card, Form, message } from 'antd';

import SelectTrack from '@/cps/SelectComponents/SelectTrack';
import styles from './Output.less';

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
      listData: [],
      selectedRowKeys: [],
      showSelectTrack: false
    };

    props.getContext && props.getContext(this);
  }

  resetInitial = () => {
    this.clearRowSelect();
    this.setState({
      listData: []
    });
  }

  getListData = () => {
    return this.state.listData;
  }

  // 删除
  onDelete = () => {
    const { listData, selectedRowKeys } = this.state;
    if (selectedRowKeys.length <= 0) {
      message.info('请至少选择一项');
    } else {
      let newData = [...listData];
      let newSelectedRowKeys = [...selectedRowKeys];
      selectedRowKeys.forEach((key) => {
        newData = newData.filter(item => item.billno !== key);
        newSelectedRowKeys = newSelectedRowKeys.filter(item => item !== key);
      });
      this.setState({
        listData: newData,
        selectedRowKeys: newSelectedRowKeys
      });
    }
  }

  // 去新增
  gotoAdd = () => {
    this.setState({ showSelectTrack: true });
  }

  // 去编辑
  gotoEdit = () => {
    //
  }

  // 选中行
  handleRowSelectChange = (selectedRowKeys) => {
    this.setState({ selectedRowKeys });
  }

  // 清除选中的行
  clearRowSelect = () => {
    this.setState({ selectedRowKeys: [] });
  }

  // 设置列表数据
  pushList = (data) => {
    const { listData } = this.state;
    const newData = [];
    const hash = {};
    [...data, ...listData].forEach((item) => {
      if (!hash[item.billno]) {
        newData.push(item);
        hash[item.billno] = true;
      }
    });
    this.setState({ listData: newData });
  }

  renderOperator() {
    return (
      <div className={styles.tableListOperator}>
        <Button icon="plus" type="primary" onClick={this.gotoAdd}>
          选择细码
        </Button>
        <Button icon="delete" type="danger" onClick={this.onDelete}>
          删除
        </Button>
      </div>
    );
  }

  render() {
    const { listData, selectedRowKeys } = this.state;
    return (
      <div className={styles.tableList}>
        <Spin spinning={false}>
          <Card title="出库细码" bordered={false}>
            {this.renderOperator()}
            <Table
              scroll={{ x: 1750 }}
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
        <SelectTrack
          visible={this.state.showSelectTrack}
          selectType="checkbox"
          handleVisible={bool => this.setState({ showSelectTrack: bool })}
          handleOk={this.pushList}
        />
      </div>
    );
  }
}

export default TableList;
