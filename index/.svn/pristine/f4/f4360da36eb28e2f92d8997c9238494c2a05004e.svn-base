import React from 'react';
import { Button } from 'antd';
import Link from 'umi/link';
import Result from '@/components/Result';
import styles from './TakePasswordResult.less';

const actions = (
  <div className={styles.actions}>
    <Link to="/user/login">
      <Button size="large" type="primary">
        去登录
      </Button>
    </Link>
    <Link to="/user/login">
      <Button size="large">返回</Button>
    </Link>
  </div>
);

const RegisterResult = ({ location }) => (
  <Result
    className={styles.registerResult}
    type="success"
    title={
      <div className={styles.title}>
        你的账户：
        {location.state ? location.state.account : '***'}
        找回密码成功
      </div>
    }
    actions={actions}
    style={{ marginTop: 56 }}
  />
);

export default RegisterResult;
