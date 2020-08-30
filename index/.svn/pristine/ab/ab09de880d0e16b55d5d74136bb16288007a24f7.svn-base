import React, { Component } from 'react';
import { Form, Input, Button, message } from 'antd';
import { connect } from 'dva';
import find from 'lodash/find';

import http from '@/utils/http';
import styles from './BaseView.less';
import GeographicView from './GeographicView';
import AvatarPicker from '@/cps/AvatarPicker';

const FormItem = Form.Item;

const validatorGeographic = (rule, value, callback) => {
  if (!value) {
    callback();
    return;
  }
  const { province, city } = value;
  if (!province.key) {
    callback('请选择所在省!');
  }
  if (!city.key) {
    callback('请选择所在市!');
  }
  callback();
};

@connect(({ user }) => ({
  currentUser: user.currentUser
}))
@Form.create()
class BaseView extends Component {
  state = {
    setBaseInfoLoading: false
  }

  componentDidMount() {
    this.setFieldsValue();
  }

  setFieldsValue = () => {
    const { currentUser, form } = this.props;
    const obj = {};
    Object.keys(form.getFieldsValue()).forEach((key) => {
      obj[key] = currentUser[key] || null;
    });
    form.setFieldsValue(obj);
  }

  onLoaded = (province, city) => {
    const { currentUser, form } = this.props;
    const provinceItem = find(province, { name: currentUser.province });
    if (!provinceItem) return;
    const cityList = city[provinceItem.id];
    const cityItem = find(cityList, { name: currentUser.city }) || {};
    form.setFieldsValue({
      geographic: {
        province: {
          label: provinceItem.name || '',
          key: provinceItem.id || ''
        },
        city: {
          label: cityItem.name || '',
          key: cityItem.id || ''
        }
      }
    });
  }

  getViewDom = (ref) => {
    this.view = ref;
  }

  handleSubmit = (e) => {
    e.preventDefault();
    const { dispatch, form, currentUser } = this.props;
    form.validateFields({ force: true }, (err, values) => {
      if (!err) {
        const params = {
          usercode: currentUser.billno,
          username: values.username,
          phone: values.phone,
          tel: values.tel,
          memo: values.memo,
          province: values.geographic.province.label,
          city: values.geographic.city.label,
          address: values.address,
          avatar: this.avatar || currentUser.avatar // 如果有更改到头像 测试(https://json.kassor.cn/team/headers/Img20171010111054.jpg)
        };
        this.setState({ setBaseInfoLoading: true });
        http({
          api: 'set_baseinfo',
          params
        }).then((result) => {
          if (result.status === '0') {
            dispatch({
              type: 'user/changeCurrentUser',
              payload: result.data
            });
            message.success('修改成功');
          } else {
            message.warn(result.msg);
          }
        }).catch(() => {
          //
        }).then(() => {
          this.setState({ setBaseInfoLoading: false });
        });
      }
    });
  }

  render() {
    const {
      currentUser,
      form: { getFieldDecorator }
    } = this.props;
    return (
      <div className={styles.baseView} ref={this.getViewDom}>
        <div className={styles.left}>
          <Form layout="vertical" onSubmit={this.handleSubmit} hideRequiredMark>
            <FormItem label="昵称">
              {getFieldDecorator('username', {
                rules: [
                  {
                    required: true,
                    message: '请输入您的昵称!'
                  }
                ]
              })(<Input />)}
            </FormItem>
            <FormItem label="个人简介">
              {getFieldDecorator('memo', {
                rules: []
              })(
                <Input.TextArea
                  placeholder="个人简介"
                  rows={4}
                />,
              )}
            </FormItem>
            <FormItem label="所在省市">
              {getFieldDecorator('geographic', {
                rules: [
                  {
                    required: true,
                    message: '请选择您的所在省市!'
                  },
                  {
                    validator: validatorGeographic
                  }
                ]
              })(<GeographicView onLoaded={this.onLoaded} />)}
            </FormItem>
            <FormItem label="街道地址">
              {getFieldDecorator('address', {
                rules: []
              })(<Input />)}
            </FormItem>
            <FormItem label="联系手机">
              {getFieldDecorator('phone', {
                rules: []
              })(<Input />)}
            </FormItem>
            <FormItem label="联系电话">
              {getFieldDecorator('tel', {
                rules: []
              })(<Input />)}
            </FormItem>
            <Button type="primary" htmlType="submit" loading={this.state.setBaseInfoLoading}>
              更新基本信息
            </Button>
          </Form>
        </div>
        <div className={styles.right}>
          <div className={styles.avatar_title}>头像</div>
          <AvatarPicker
            initialValue={currentUser.avatar || require('@/assets/logo.png')}
            onChange={value => this.avatar = value}
          />
        </div>
      </div>
    );
  }
}

export default BaseView;
