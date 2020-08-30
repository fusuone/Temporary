import React, { Component, Fragment } from 'react';
import { Icon, List, message } from 'antd';
import { connect } from 'dva';

@connect(({ user }) => ({
  currentUser: user.currentUser
}))
class BindingView extends Component {
  getData = () => {
    const { userno = '' } = this.props.currentUser;
    const phone = userno ? `${userno.substr(0, 3)}****${userno.substr(7, 4)}` : '';
    return [
      {
        title: '绑定手机',
        description: phone || '当前未绑定手机',
        actions: [<a onClick={this.gotoBindPhone}>{phone ? '更换' : '绑定'}</a>],
        avatar: <Icon type="mobile" className="alipay" />
      },
      {
        title: '绑定邮箱',
        description: '当前未绑定邮箱',
        actions: [<a>绑定</a>],
        avatar: <Icon type="mail" className="alipay" />
      }
    ];
  }

  gotoBindPhone = () => {
    if (this.props.currentUser.userno) {
      message.warn('暂不支持更换手机号');
    }
  }

  render() {
    return (
      <Fragment>
        <List
          itemLayout="horizontal"
          dataSource={this.getData()}
          renderItem={item => (
            <List.Item actions={item.actions}>
              <List.Item.Meta
                avatar={item.avatar}
                title={item.title}
                description={item.description}
              />
            </List.Item>
          )}
        />
      </Fragment>
    );
  }
}

export default BindingView;
