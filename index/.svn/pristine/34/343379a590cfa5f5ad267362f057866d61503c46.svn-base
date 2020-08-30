import React, { Component } from 'react';
import { connect } from 'dva';
import Link from 'umi/link';
import { Form, Input, Button, Checkbox, Alert, message, Icon } from 'antd';
import styles from './Login.less';

const FormItem = Form.Item;

@connect(({ login, loading }) => ({
  login,
  submitting: loading.effects['login/login']
}))
@Form.create()
class LoginPage extends Component {
  state = {
    autoLogin: true
  };

  otherLogin = () => {
    message.warning('暂不支持第三方登录');
  }

  handleSubmit = (e) => {
    e.preventDefault();
    const { form, dispatch } = this.props;
    form.validateFields({ force: true }, (err, values) => {
      if (!err) {
        const { uid, upass } = values;
        dispatch({
          type: 'login/login',
          payload: { uid, upass }
        });
      }
    });
  };

  changeAutoLogin = (e) => {
    this.setState({
      autoLogin: e.target.checked
    });
  };

  renderMessage = content => (
    <Alert style={{ marginBottom: 24 }} message={content} type="error" showIcon />
  );

  render() {
    const { login, submitting, form } = this.props;
    const { autoLogin } = this.state;
    const { getFieldDecorator } = form;
    return (
      <div className={styles.main}>
        <h3>登录</h3>
        <Form onSubmit={this.handleSubmit}>
          {
            login.status === 'error'
            && !submitting
            && this.renderMessage(login.errmsg)
          }
          <FormItem>
            {getFieldDecorator('uid', {
              rules: [{
                required: true,
                message: '请输入账户！'
              }]
            })(
              <Input
                size="large"
                prefix={<Icon type="user" className={styles.prefixIcon} />}
                placeholder="账户"
              />,
            )}
          </FormItem>
          <FormItem>
            {getFieldDecorator('upass', {
              rules: [{
                required: true,
                message: '请输入密码！'
              }]
            })(
              <Input
                size="large"
                prefix={<Icon type="lock" className={styles.prefixIcon} />}
                type="password"
                placeholder="密码"
              />,
            )}
          </FormItem>
          <div>
            <Checkbox checked={autoLogin} onChange={this.changeAutoLogin}>
              自动登录
            </Checkbox>
            <Link style={{ float: 'right' }} to="/user/take-password">
              忘记密码
            </Link>
          </div>
          <FormItem>
            <Button size="large" className={styles.submit} type="primary" htmlType="submit" loading={submitting}>
              登录
            </Button>
          </FormItem>
          <div className={styles.other}>
            其他登录方式
            <Icon className={styles.icon} type="wechat" theme="filled" onClick={this.otherLogin} />
            <Link className={styles.register} to="/user/register">
              注册账户
            </Link>
          </div>
        </Form>
      </div>
    );
  }
}

export default LoginPage;
