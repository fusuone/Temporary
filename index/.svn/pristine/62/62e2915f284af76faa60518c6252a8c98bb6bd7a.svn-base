import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, message, Form, Input, Select } from 'antd';

import http from '@/utils/http';
import config from '@/common/config';

const workerMaps = { ...config.workerMaps };
delete workerMaps['100'];

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class WorkerAdd extends PureComponent {
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
        bno: pageType === 'add' ? '' : this.itemData.billno,
        admin: currentUser.admin,
        ...values
      };
      http({
        method: 'post',
        api: 'setworker',
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
    const title = pageType === 'add' ? '新增工作人员' : '修改工作人员';
    return (
      <Card title={title} bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="工种">
                {getFieldDecorator('wtype', {
                  rules: [{ required: true, message: '请选择一项！' }]
                })(
                  <Select>
                    {Object.keys(workerMaps).map(v => (
                      <Select.Option value={workerMaps[v]}>{workerMaps[v]}</Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="姓名">
                {getFieldDecorator('worker', {
                  rules: [{ required: true, message: '请输入姓名！' }]
                })(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="联系电话">
                {getFieldDecorator('phone')(
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

export default WorkerAdd;
