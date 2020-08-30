import React, { PureComponent } from 'react';
import { connect } from 'dva';
import PropTypes from 'prop-types';
import { Row, Col, Modal, message, Form, Input, DatePicker } from 'antd';
import moment from 'moment';

import http from '@/utils/http';


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
    const revedate = moment(activeItem.revedate);
    this.props.form.setFieldsValue({
      custno: activeItem.custno,
      custname: activeItem.custname,
      billdate: billdate.isValid() ? billdate : undefined,
      crudeno: activeItem.crudeno,
      suffixno: activeItem.suffixno,
      qty: activeItem.qty,
      extent: activeItem.extent,
      unit: activeItem.unit,
      class: activeItem.class,
      factory: activeItem.factory,
      factoryno: activeItem.factoryno,
      reveuser: activeItem.reveuser,
      revedate: revedate.isValid() ? revedate : undefined,
      revecar: activeItem.revecar,
      reveplate: activeItem.reveplate,
      driver: activeItem.driver,
      artname: activeItem.model,
      remark: activeItem.remark,
      // 选择的工艺信息
      cpsl: activeItem.cpsl,
      cpmf: activeItem.cpmf,
      stretch: activeItem.stretch,
      veins: activeItem.veins,
      processtxt: activeItem.processtxt,
      increq: activeItem.increq,
      vision: activeItem.vision,
      attach: activeItem.attach
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
        admin: currentUser.admin,
        usercode: currentUser.billno,
        username: currentUser.username,
        ...values,
        billdate: values.billdate.format('YYYY-MM-DD HH:mm:ss'),
        revedate: values.revedate && values.revedate.format('YYYY-MM-DD HH:mm:ss')
      };
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
      form: { getFieldDecorator }
    } = this.props;
    const { submitting } = this.state;
    const colProps = { md: 6, xs: 12 };
    return (
      <Modal
        title={`${addOrEdit === '0' ? '填写' : '修改'}工作任务`}
        width="50%"
        maskClosable={false}
        visible={this.props.visible}
        onCancel={this.handleCancel}
        onOk={this.handleSubmit}
        confirmLoading={submitting}
        afterClose={this.handleAfterClose}
      >
        <Form layout="vertical">
          <Row gutter={24}>
            <Col>
              <Form.Item label="任务标题">
                {getFieldDecorator('staffno', {
                  rules: [{ required: true, message: '请输入任务标题' }]
                })(
                  <Input placeholder="请输入任务标题" />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="截止日期">
                {getFieldDecorator('billdate', {
                  initialValue: moment(),
                  rules: [{ required: true, message: '请选择制单日期' }]
                })(
                  <DatePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="任务成员">
                {getFieldDecorator('ruletype')(<Input />)}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="任务描述">
                {getFieldDecorator('rulename')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col>
              <Form.Item label="相关图片">
                {getFieldDecorator('points')(<Input />)}
              </Form.Item>
            </Col>
          </Row>
        </Form>
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
