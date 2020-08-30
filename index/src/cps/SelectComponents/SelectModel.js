// 选择工艺规格
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Modal, message, Spin, Table, Button, Input, Form } from 'antd';

import http from '@/utils/http';
import config from '@/common/config';

import ModalTitle from '../ModalTitle';
import styles from './SelectModel.less';

const initialSearchParams = {
  artname: '',
  custname: ''
};

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class SelectModel extends PureComponent {
  constructor(props) {
    super(props);
    this.columns = [{
      title: '品名',
      dataIndex: 'artname',
      width: 130
    }, {
      title: '缸号',
      dataIndex: 'suffixno',
      width: 130
    }, {
      title: '客户名称',
      dataIndex: 'custname',
      width: 130
    }, {
      title: '加工工序',
      dataIndex: 'jggy',
      width: 130
    }, {
      title: '颜色',
      dataIndex: 'color',
      width: 80
    }, {
      title: '拉抻',
      dataIndex: 'stretch',
      width: 80
    }, {
      title: '缩率',
      dataIndex: 'cpsl',
      width: 80
    },
    {
      title: '斜纹',
      dataIndex: 'veins',
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
      title: '成品门幅',
      dataIndex: 'cpmf',
      width: 80
    }, {
      title: '并匹',
      dataIndex: 'attach',
      width: 70,
      render: text => (text === '1' ? <span>是</span> : <span>否</span>)
    }, {
      title: '机型',
      dataIndex: 'ptype',
      width: 130
    }, {
      title: '工艺种类',
      dataIndex: 'processtype',
      width: 130
    }, {
      title: '加艺信息',
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
        artname: fieldsValue.artname,
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
    const item = this.state.selectedRows[0];
    if (item) {
      handleOk(item);
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
    if (this.state.loading) return;
    this.clearRowSelect();
    this.setState({ loading: true });
    http({
      method: 'get',
      api: 'getartlist',
      params: this.state.reqParams
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
              <Form.Item label="品名">
                {getFieldDecorator('artname')(
                  <Input />
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
        title={<ModalTitle title="选择工艺规格" onReload={this.onRefresh} onClose={this.handleCancel} />}
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
                  this.handleRowSelectChange([record.billno], [record]);
                }
              })}
              rowSelection={{
                type: 'radio',
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

export default SelectModel;
