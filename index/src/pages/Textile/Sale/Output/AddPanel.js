import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, message, Form, Input, DatePicker, Select, Icon } from 'antd';
import router from 'umi/router';
import moment from 'moment';

import http from '@/utils/http';

import SelectDepot from '@/cps/SelectComponents/SelectDepot';
import SelectWorker from '@/cps/SelectComponents/SelectWorker';
import SelectCustomer from '@/cps/SelectComponents/SelectCustomer';
import SelectGecarplate from '@/cps/SelectComponents/SelectGecarplate';

const invoiceTypes = ['普通发票', '专用发票', '地税发票', '无发票'];
const periodTypes = ['现金', '3天', '7天', '10天', '15天', '20天', '30天', '45天', '60天'];
const billStatusTypes = ['新开单', '已开票', '已审核', '未收款', '已收款', '未付款', '已付款'];

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class OutputAdd extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,
      isHasInvoice: true,

      selectWorkerType: '',
      showSelectDepot: false,
      showSelectWorker: false,
      showSelectCustomer: false,
      showSelectGecarplate: false
    };

    props.getContext && props.getContext(this);
  }

  changeInvoiceType = async (value) => {
    if (value === '无发票') {
      await this.setState({ isHasInvoice: false });
      this.props.form.setFieldsValue({ invoice: '' });
    } else {
      await this.setState({ isHasInvoice: true });
    }
    // this.props.form.validateFields(['invoice'], { force: true });
  }

  resetForm = () => {
    this.props.form.resetFields();
    this.changeInvoiceType(invoiceTypes[0]);
  }

  // 提交
  handleSubmit = (e) => {
    e.preventDefault();
    const {
      currentUser,
      getTableList,
      resetTableList,
      form: {
        validateFieldsAndScroll
      }
    } = this.props;
    const orderBody = getTableList();
    if (orderBody.length <= 0) {
      message.info('请先选择出库细码！');
      return;
    }
    validateFieldsAndScroll((err, values) => {
      if (err) return;
      this.setState({ submitting: true });
      const orderHead = {
        admin: currentUser.admin,
        usercode: currentUser.billno,
        username: currentUser.username,
        ...values,
        dealdate: values.dealdate.format('YYYY-MM-DD H:m:s'),
        billdate: values.billdate.format('YYYY-MM-DD H:m:s')
      };
      http({
        method: 'post',
        api: 'setsalorder',
        data: {
          head: orderHead,
          body: orderBody
        }
      }).then(({ status, msg }) => {
        if (status === '0') {
          message.success(
            <span>处理成功，
              <a onClick={() => router.push('/textile/sale/order-list')}>点击查看</a>
            </span>
          );
          this.resetForm();
          resetTableList();
        } else {
          message.warn(msg);
        }
        this.setState({ submitting: false });
      }).catch(() => {
        this.setState({ submitting: false });
      });
    });
  }

  render() {
    const {
      form: { getFieldDecorator, setFieldsValue }
    } = this.props;
    const { submitting } = this.state;
    const colProps = { md: 6, xs: 12 };
    return (
      <Card title="填写出库信息" bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="客户">
                {getFieldDecorator('custname', {
                  rules: [{ required: true, message: '请选择客户' }]
                })(
                  <Input
                    readOnly
                    placeholder="请选择客户"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectCustomer: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="客户编号" style={{ display: 'none' }}>
                {getFieldDecorator('custno')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="制单日期">
                {getFieldDecorator('billdate', {
                  initialValue: moment(),
                  rules: [{ required: true, message: '请选择交货日期' }]
                })(
                  <DatePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="账单方式">
                {getFieldDecorator('billstatus', {
                  initialValue: billStatusTypes[0]
                })(
                  <Select>
                    {billStatusTypes.map(item => (
                      <Select.Option value={item}>{item}</Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="账期">
                {getFieldDecorator('period', {
                  initialValue: periodTypes[0]
                })(
                  <Select>
                    {periodTypes.map(item => (
                      <Select.Option value={item}>{item}</Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="应收款">
                {getFieldDecorator('pay')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="已收款">
                {getFieldDecorator('apay')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="出库员">
                {getFieldDecorator('operator')(
                  <Input
                    readOnly
                    placeholder="请选择出库员"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectWorker: true, selectWorkerType: '3' })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="送货地址">
                {getFieldDecorator('destination')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="商品出货仓库">
                {getFieldDecorator('depotname')(
                  <Input
                    readOnly
                    placeholder="请选择商品出货仓库"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectDepot: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="商品出货仓库编号" style={{ display: 'none' }}>
                {getFieldDecorator('depotcode')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="送货人">
                {getFieldDecorator('driver')(
                  <Input
                    readOnly
                    placeholder="请选择送货人"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectWorker: true, selectWorkerType: '4' })}
                  />
                )}
              </Form.Item>
              <Form.Item label="送货人手机号" style={{ display: 'none' }}>
                {getFieldDecorator('driverphone')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="送货车牌">
                {getFieldDecorator('carplate')(
                  <Input
                    readOnly
                    placeholder="请选择送货车牌"
                    prefix={<Icon type="car" theme="outlined" />}
                    onClick={() => this.setState({ showSelectGecarplate: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="随车电话" style={{ display: 'none' }}>
                {getFieldDecorator('carphone')(<Input />)}
              </Form.Item>
              <Form.Item label="车型" style={{ display: 'none' }}>
                {getFieldDecorator('carmodel')(<Input />)}
              </Form.Item>
              <Form.Item label="使用类型" style={{ display: 'none' }}>
                {getFieldDecorator('cartype')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="交货日期">
                {getFieldDecorator('dealdate', {
                  rules: [{ required: true, message: '请选择交货日期' }]
                })(
                  <DatePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="发票类型">
                {getFieldDecorator('invtype', {
                  initialValue: invoiceTypes[0]
                })(
                  <Select onChange={this.changeInvoiceType}>
                    {invoiceTypes.map(item => (
                      <Select.Option value={item}>{item}</Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="发票编号">
                {getFieldDecorator('invoice', {
                  rules: [{ required: this.state.isHasInvoice, message: '请输入发票编号' }]
                })(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="备注">
                {getFieldDecorator('remark')(
                  <Input />
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row>
            <Button type="primary" size="large" onClick={this.handleSubmit} loading={submitting}>提交</Button>
          </Row>
        </Form>
        <SelectCustomer
          visible={this.state.showSelectCustomer}
          handleVisible={bool => this.setState({ showSelectCustomer: bool })}
          handleOk={(item) => {
            setFieldsValue({
              custno: item.billno,
              custname: item.title
            });
          }}
        />
        <SelectDepot
          visible={this.state.showSelectDepot}
          handleVisible={bool => this.setState({ showSelectDepot: bool })}
          handleOk={(item) => {
            setFieldsValue({
              depotcode: item.depotcode,
              depotname: item.depotname
            });
          }}
        />
        <SelectGecarplate
          visible={this.state.showSelectGecarplate}
          handleVisible={bool => this.setState({ showSelectGecarplate: bool })}
          handleOk={(item) => {
            setFieldsValue({
              carplate: item.reveplate,
              carmodel: item.revecar,
              cartype: item.cartype,
              carphone: item.phone
            });
          }}
        />
        <SelectWorker
          visible={this.state.showSelectWorker}
          workerType={this.state.selectWorkerType}
          handleVisible={bool => this.setState({ showSelectWorker: bool })}
          handleOk={(items) => {
            const { worker, phone } = items[0];
            if (this.state.selectWorkerType === '3') {
              setFieldsValue({
                operator: worker
              });
            } else if (this.state.selectWorkerType === '4') {
              setFieldsValue({
                driver: worker,
                driverphone: phone
              });
            }
          }}
        />
      </Card>
    );
  }
}

export default OutputAdd;
