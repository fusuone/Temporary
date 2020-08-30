import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, message, Form, Input, Select } from 'antd';
import http from '@/utils/http';
import ImagePicker from '@/cps/ImagePicker';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class CustomerAdd extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,
      pageType: 'add'
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

    // 图片
    obj.fileList = [];
    ['image1', 'image2', 'image3'].forEach((item, index) => {
      const url = this.itemData[item];
      url && obj.fileList.push({
        status: 'done',
        uid: index,
        url
      });
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

  fileListToImageFileds = (fileList) => {
    const obj = {};
    const fields = ['image1', 'image2', 'image3'];
    fileList.forEach((item, index) => {
      obj[fields[index]] = item.url;
    });
    return obj;
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
        billno: pageType === 'add' ? '' : this.itemData.billno,
        uid: currentUser.userno,
        admin: currentUser.admin,
        typename: '新客户',
        typeno: '-1',
        ...this.fileListToImageFileds(values.fileList),
        ...values
      };
      delete data.fileList;
      http({
        method: 'post',
        api: 'setcustomer',
        data
      }).then(({ status, msg }) => {
        if (status === '0') {
          message.success(msg);
          if (pageType === 'add') {
            onRefresh('reset');
            this.scrollToTop();
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
      form: { getFieldDecorator }
    } = this.props;
    const { submitting, pageType } = this.state;
    const colProps = { lg: 8, md: 12, xs: 12 };
    const title = pageType === 'add' ? '新增客户' : '修改客户';
    return (
      <Card title={title} bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="客户名称">
                {getFieldDecorator('title', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" placeholder="输入客户名称" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="客户类型">
                {getFieldDecorator('ispana', {
                  initialValue: '0'
                })(
                  <Select>
                    <Select.Option value="0">普通客户</Select.Option>
                    <Select.Option value="1">合作伙伴</Select.Option>
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="联系人">
                {getFieldDecorator('linkman')(
                  <Input type="text" placeholder="输入联系人" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="联系电话">
                {getFieldDecorator('tel')(
                  <Input type="text" placeholder="输入手机号" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="联系手机">
                {getFieldDecorator('phone')(
                  <Input type="text" placeholder="输入电话" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="地址">
                {getFieldDecorator('address')(
                  <Input type="text" placeholder="输入地址" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="传真">
                {getFieldDecorator('fax')(
                  <Input type="text" placeholder="输入传真" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="邮编">
                {getFieldDecorator('zipcode')(
                  <Input type="text" placeholder="输入邮编" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="Email">
                {getFieldDecorator('email')(
                  <Input type="text" placeholder="输入Email" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="营业执照">
                {getFieldDecorator('taxno')(
                  <Input type="text" placeholder="输入营业执照" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="法人代表">
                {getFieldDecorator('legal_representative')(
                  <Input type="text" placeholder="输入法人代表" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="银行账号">
                {getFieldDecorator('cardno')(
                  <Input type="text" placeholder="输入银行账号" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="开户行">
                {getFieldDecorator('bank')(
                  <Input type="text" placeholder="输入开户行" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="发票地址">
                {getFieldDecorator('invoice_address')(
                  <Input type="text" placeholder="输入发票地址" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="备注">
                {getFieldDecorator('remark')(
                  <Input type="text" placeholder="填写备注" />
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row>
            <Form.Item label="图片(3张)">
              {getFieldDecorator('fileList')(
                <ImagePicker />
              )}
            </Form.Item>
          </Row>
          <Row>
            <Button type="primary" size="large" onClick={this.handleSubmit} loading={submitting}>保存</Button>
          </Row>
        </Form>
      </Card>
    );
  }
}

export default CustomerAdd;
