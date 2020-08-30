import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, message, Form, Input, Select, Icon } from 'antd';
import http from '@/utils/http';

import SelectCustomer from '@/cps/SelectComponents/SelectCustomer';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class ArtAdd extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,
      pageType: 'add',
      showSelectCustomer: false
    };
    this.itemData = {};
    props.getContext && props.getContext(this);
  }

  setFieldsValue = () => {
    const { setFieldsValue, getFieldsValue } = this.props.form;
    const obj = {};
    Object.keys(getFieldsValue()).forEach((key) => {
      obj[key] = this.itemData[key] || '';
    });
    setFieldsValue(obj);
  }

  // 切换
  togglePage = (type, item) => {
    if (this.state.submitting) {
      message.info('正在处理，请稍等');
      return;
    }
    const { resetFields } = this.props.form;
    resetFields();
    this.scrollToTop();
    if (type === 'add') {
      this.setState({ pageType: 'add' });
      this.itemData = {};
    } else {
      this.setState({ pageType: 'edit' });
      this.itemData = item;
      this.setFieldsValue();
    }
  }

  scrollToTop = () => {
    window.scrollTo(0, 0);
  }

  // 提交
  handleSubmit = (e) => {
    e.preventDefault();
    const {
      currentUser,
      onRefresh = () => null,
      form: {
        validateFieldsAndScroll,
        resetFields
      }
    } = this.props;
    const { pageType } = this.state;
    validateFieldsAndScroll((err, values) => {
      if (err) return;
      this.setState({ submitting: true });
      const data = {
        bno: pageType === 'add' ? '' : this.itemData.billno,
        uid: currentUser.userno,
        admin: currentUser.admin,
        ...values
      };
      http({
        method: 'post',
        api: 'setart',
        data
      }).then(({ status, msg }) => {
        if (status === '0') {
          message.success(msg);
          if (pageType === 'add') {
            onRefresh('reset');
            resetFields();
          } else {
            onRefresh();
          }
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
    const { submitting, pageType } = this.state;
    const colProps = { md: 6, xs: 12 };
    const title = pageType === 'add' ? '新增规格工艺' : '修改规格工艺';
    return (
      <Card title={title} bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="品名">
                {getFieldDecorator('artname', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" placeholder="输入品名" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="缸号">
                {getFieldDecorator('suffixno')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="加工工序">
                {getFieldDecorator('jggy')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="颜色">
                {getFieldDecorator('color')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="成本缩率">
                {getFieldDecorator('cpsl')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="成品拉斜">
                {getFieldDecorator('stretch')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="斜纹">
                {getFieldDecorator('veins')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="经向">
                {getFieldDecorator('vertical')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="纬向">
                {getFieldDecorator('weft')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="加减码">
                {getFieldDecorator('increq')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="客户编号" style={{ display: 'none' }}>
                {getFieldDecorator('custno')(<Input />)}
              </Form.Item>
              <Form.Item label="客户名称">
                {getFieldDecorator('custname')(
                  <Input
                    readOnly
                    placeholder="请选择客户"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectCustomer: true })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="并匹要求">
                {getFieldDecorator('attach', {
                  initialValue: '0'
                })(
                  <Select>
                    <Select.Option value="1">允许并匹</Select.Option>
                    <Select.Option value="0">不许并匹</Select.Option>
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="工艺种类">
                {getFieldDecorator('processtype')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="工艺信息">
                {getFieldDecorator('processtxt')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="工艺备注">
                {getFieldDecorator('processremark')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="备注">
                {getFieldDecorator('remark')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row>
            <Button type="primary" size="large" onClick={this.handleSubmit} loading={submitting}>保存</Button>
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
      </Card>
    );
  }
}

export default ArtAdd;
