/* eslint-disable comma-dangle */
import React, { PureComponent } from 'react';
import { connect } from 'dva';
import PropTypes from 'prop-types';
import { Row, Col, Modal, message, Form, DatePicker, Input, Icon } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import SelectRule from '@/cps/SelectComponents/SelectRule';
import SelectPointRule from '@/cps/SelectComponents/SelectPointRule';

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
      submitting: false
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
        staffname: currentUser.staffname,
        ruletype: values.rule ? values.rule[0].rulename : '',
        ...values,
        billdate: values.billdate.format('YYYY-MM-DD HH:mm:ss')
      };
      delete data.rule;

      http({
        method: 'post',
        api: 'setpointtrack',
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
    const colProps = { md: 8, xs: 12 };
    return (
      <Modal
        title={`${addOrEdit === '0' ? '填写' : '修改'}录入资料`}
        maskClosable={false}
        width="50%"
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleSubmit}
        confirmLoading={submitting}
        afterClose={this.handleAfterClose}
      >
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="人员名称">
                {getFieldDecorator('staffname', {
                  rules: [{ required: true, message: '请选择客户' }]
                })(
                  <Input
                    readOnly
                    placeholder="请选择客户"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectRule: true })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="人员编号" style={{ display: 'none' }}>
                {getFieldDecorator('staffno', {
                  rules: [{ required: true, message: '请选择客户' }]
                })(
                  <Input
                    readOnly
                    placeholder="请选择客户"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectRule: true })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="制单日期" style={{ display: 'none' }}>
                {getFieldDecorator('billdate', {
                  initialValue: moment(),
                  rules: [{ required: true, message: '请选择制单日期' }]
                })(
                  <DatePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="规则分类">
                {getFieldDecorator('rule')(<SelectPointRule style={{ width: '100%' }} />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="规则名称">
                {getFieldDecorator('rulename')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="积分">
                {getFieldDecorator('points', {
                  rules: [{ required: true, message: '请输入人员名称' }]
                })(<Input />)}
              </Form.Item>
            </Col>
            <Col md={16} sm={24}>
              <Form.Item label="规则说明">
                {getFieldDecorator('remark')(
                  <Input />
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
