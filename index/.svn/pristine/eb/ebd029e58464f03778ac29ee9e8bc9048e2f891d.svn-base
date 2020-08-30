import React, { Component, Fragment } from 'react';
import { List } from 'antd';
import { connect } from 'dva';

const passwordStrength = {
  strong: (
    <font className="strong">强</font>
  ),
  medium: (
    <font className="medium">中</font>
  ),
  weak: (
    <font className="weak">弱</font>
  )
};

@connect(({ user }) => ({
  currentUser: user.currentUser
}))
class SecurityView extends Component {
  getData = () => {
    const { pw_strength } = this.props.currentUser;
    const pwStrength = pw_strength
      ? passwordStrength[pw_strength] || passwordStrength['weak']
      : passwordStrength['weak'];
    return [
      {
        title: '账户密码',
        description: <Fragment>当前密码强度：{pwStrength}</Fragment>,
        actions: [<a>修改</a>]
      }
    ];
  };

  render() {
    return (
      <Fragment>
        <List
          itemLayout="horizontal"
          dataSource={this.getData()}
          renderItem={item => (
            <List.Item actions={item.actions}>
              <List.Item.Meta title={item.title} description={item.description} />
            </List.Item>
          )}
        />
      </Fragment>
    );
  }
}

export default SecurityView;
