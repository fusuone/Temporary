import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, message, Form, Input } from 'antd';

import http from '@/utils/http';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class DepotAdd extends PureComponent {
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
        admin: pageType === 'add' ? '' : this.itemData.id,
        usercode: currentUser.billno,
        ...values
      };
      http({
        method: 'post',
        api: 'setdepot',
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
      form: { getFieldDecorator }
    } = this.props;
    const { submitting, pageType } = this.state;
    const colProps = { md: 6, xs: 12 };
    const title = pageType === 'add' ? '新增仓库' : '修改仓库';
    return (
      <Card title={title} bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="仓库编号">
                {getFieldDecorator('depotcode', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="仓库名称">
                {getFieldDecorator('depotname', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="联系人">
                {getFieldDecorator('linkman')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="联系电话">
                {getFieldDecorator('linkphone')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="地址">
                {getFieldDecorator('address')(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row>
            <Button type="primary" size="large" onClick={this.handleSubmit} loading={submitting}>保存</Button>
          </Row>
        </Form>
      </Card>
    );
  }
}

export default DepotAdd;
