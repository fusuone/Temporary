/* eslint-disable comma-dangle */
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import PropTypes from 'prop-types';
import { Row, Col, Modal, message, Select, Form, Input, Icon } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import SelectRule from '@/cps/SelectComponents/SelectRule';

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
      SelectRule: false
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.visible && nextProps.visible !== this.props.visible) {
      if (nextProps.addOrEdit === '1') {
        this.setFieldsValue(nextProps);
      }
    }
  }

  setFieldsValue = (props) => {
    const { activeItem } = props;
    const billdate = moment(activeItem.billdate);
    this.props.form.setFieldsValue({
      userno: activeItem.userno,
      staffno: activeItem.billno,
      billdate: billdate.isValid() ? billdate : undefined,
      staffname: activeItem.username,
      currpoints: activeItem.currpoints,
      division: activeItem.division
    });
  }

  handleCancel = () => {
    const { handleVisible = () => null } = this.props;
    handleVisible(false);
  }

  // 关闭之后
  handleAfterClose = () => {
    this.props.form.resetFields();
  }

  // 提交
  handleSubmit = (e) => {
    e.preventDefault();
    const {
      currentUser,
      addOrEdit,
      activeItem,
      handleRefresh = () => null,
      form: {
        validateFieldsAndScroll
      }
    } = this.props;
    validateFieldsAndScroll((err, values) => {
      if (err) return;
      this.setState({ submitting: true });
      const data = {
        flat: addOrEdit, // 0新增 1编辑
        billno: activeItem.billno,
        uid: currentUser.userno,
        admin: currentUser.admin,
        usercode: currentUser.billno,
        username: currentUser.username,
        ruletype: values.rule ? values.rule[0].rulename : '',
        ...values,
        billdate: values.billdate.format('YYYY-MM-DD HH:mm:ss')
      };
      delete data.rule;

      http({
        method: 'post',
        api: 'setpointrule',
        data
      }).then(({ status, msg }) => {
        if (status === '0') {
          message.success(msg);
          this.handleCancel();
          handleRefresh();
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
      addOrEdit,
      form: { getFieldDecorator, setFieldsValue }
    } = this.props;
    const { submitting } = this.state;
    return (
      <Modal
        title={`${addOrEdit === '0' ? '添加' : '修改'}队员录入资料`}
        maskClosable={false}
        width="40%"
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleSubmit}
        confirmLoading={submitting}
        afterClose={this.handleAfterClose}
      >
        <Form layout="vertical">
          <Row gutter={24}>
            <Col>
              <Form.Item label="队员号码">
                {getFieldDecorator('tuserno', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" style={{ width: 300 }} />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="部门名称">
                {getFieldDecorator('teamname', {
                  rules: [{ required: true, message: '不能为空！' }]
                })(
                  <Input type="text" style={{ width: 300 }} />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="人员名称">
                {getFieldDecorator('username')(
                  <Input
                    readOnly
                    placeholder="请选择"
                    style={{ width: 300 }}
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectRule: true })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="部门职能">
                {getFieldDecorator('job')(
                  <Select type="text" style={{ width: 300 }}>
                    <Option value="销售">销售</Option>
                    <Option value="财务">财务</Option>
                    <Option value="仓库">仓库</Option>
                  </Select>
                )}
              </Form.Item>
            </Col>

          </Row>
        </Form>
        <SelectRule
          visible={this.state.showSelectRule}
          handleVisible={bool => this.setState({ showSelectRule: bool })}
          handleOk={(item) => {
            setFieldsValue({
              staffname: item.username,
              staffno: item.billno
            });
          }}
        />

      </Modal>
    );
  }
}

RuleAdd.propTypes = {
  addOrEdit: PropTypes.oneOf(['0', '1']) // 0新增 1编辑
};

RuleAdd.defaultProps = {
  addOrEdit: '0'
};


export default RuleAdd;
