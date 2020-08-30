import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Modal, message, Form, Input, Icon } from 'antd';

import http from '@/utils/http';
import SelectWorker from '@/cps/SelectComponents/SelectWorker';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class DataInput extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,
      showSelectWorker: false
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.visible && nextProps.visible !== this.props.visible) {
      this.setFieldsValue(nextProps);
    }
  }

  setFieldsValue = (props) => {
    const { activeItem } = props;
    this.props.form.setFieldsValue({
      crudeno: activeItem.crudeno,
      suffixno: activeItem.suffixno,
      model: activeItem.model,
      color: activeItem.color,
      increq: activeItem.increq,
      indexno: activeItem.indexno,
      machineno: activeItem.machineno
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
        admin: currentUser.admin,
        usercode: currentUser.billno,
        username: currentUser.username,
        crude_item: activeItem,
        machineno: values.machineno,
        increq: values.increq,
        qty: values.qty,
        worker: values.worker,
        workerno: values.workerno,
        remark: values.remark
      };
      http({
        method: 'post',
        api: 'settrack',
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
      form: { getFieldDecorator, setFieldsValue }
    } = this.props;
    const { submitting } = this.state;
    const colProps = { md: 6, xs: 12 };
    return (
      <Modal
        title="确认细码信息"
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
            <Col {...colProps}>
              <Form.Item label="单号">
                {getFieldDecorator('crudeno')(
                  <Input disabled />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="缸号">
                {getFieldDecorator('suffixno')(
                  <Input disabled />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="规格">
                {getFieldDecorator('model')(
                  <Input disabled />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="颜色">
                {getFieldDecorator('color')(
                  <Input disabled />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="新匹号">
                {getFieldDecorator('indexno')(
                  <Input disabled />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="机台号">
                {getFieldDecorator('machineno')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="加减码">
                {getFieldDecorator('increq')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="标签长度">
                {getFieldDecorator('qty')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="验布员">
                {getFieldDecorator('worker', {
                  rules: [{ required: true, message: '请选择验布员' }]
                })(
                  <Input
                    readOnly
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectWorker: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="验布员编号" style={{ display: 'none' }}>
                {getFieldDecorator('workerno')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="备注">
                {getFieldDecorator('remark')(
                  <Input />
                )}
              </Form.Item>
            </Col>
          </Row>
        </Form>
        <SelectWorker
          visible={this.state.showSelectWorker}
          workerType="0"
          handleVisible={bool => this.setState({ showSelectWorker: bool })}
          handleOk={(items) => {
            const { billno, worker } = items[0];
            setFieldsValue({
              workerno: billno,
              worker
            });
          }}
        />
      </Modal>
    );
  }
}

export default DataInput;
