import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, Icon, message, Select, Form, Input } from 'antd';
import SelectRule from '@/cps/SelectComponents/SelectRule';
import http from '@/utils/http';

const { Option } = Select;
@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class RuleAdd extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,
      pageType: 'add',
      showSelectRule: false
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
        bno: pageType === 'add' ? '' : this.itemData.id,
        tuserno: currentUser.tuserno,
        flag: 1,
        admin: currentUser.admin,
        usercode: currentUser.billno,
        ...values
      };
      http({
        method: 'post',
        api: 'addgroupuser',
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
    const title = pageType === 'add' ? '添加队员信息' : '修改队员信息';
    return (
      <Card title={title} bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="队员号码">
                {getFieldDecorator('tuserno', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="部门名称">
                {getFieldDecorator('teamname', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" />
                )}
              </Form.Item>
            </Col>
            <Col md={8} sm={24}>
              <Form.Item label="人员名称">
                {getFieldDecorator('username')(
                  <Input
                    readOnly
                    placeholder="请选择"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectRule: true })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="部门职能"style={{width:20}}>
                {getFieldDecorator('job')(
                  <Select type="text">
                    <Option value="销售">销售</Option>
                    <Option value="财务">财务</Option>
                    <Option value="仓库">仓库</Option>
                  </Select>
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row>
            <Button type="primary" size="large" onClick={this.handleSubmit} loading={submitting}>保存</Button>
          </Row>
        </Form>
        <SelectRule
          visible={this.state.showSelectRule}
          handleVisible={bool => this.setState({ showSelectRule: bool })}
          handleOk={(item) => {
            setFieldsValue({
              username: item.username
            });
          }}
        />
      </Card>
    );
  }
}

export default RuleAdd;
