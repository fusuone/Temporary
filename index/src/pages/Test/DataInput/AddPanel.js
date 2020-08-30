import React, { PureComponent } from 'react';
import { connect } from 'dva';
import PropTypes from 'prop-types';
import { Row, Col, Modal, message, Form, Input, DatePicker, Select, Icon, Divider } from 'antd';
import moment from 'moment';

import http from '@/utils/http';
import SelectDepot from '@/cps/SelectComponents/SelectDepot';
import SelectModel from '@/cps/SelectComponents/SelectModel';
import SelectWorker from '@/cps/SelectComponents/SelectWorker';
import SelectCustomer from '@/cps/SelectComponents/SelectCustomer';
import SelectGecarplate from '@/cps/SelectComponents/SelectGecarplate';
import styles from './DataInput.less';

const classTypes = ['正常布', '返工布', '退卷布'];

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class AddPanel extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      submitting: false,

      selectWorkerType: '',
      showSelectDepot: false,
      showSelectModel: false,
      showSelectWorker: false,
      showSelectFactory: false,
      showSelectCustomer: false,
      showSelectGecarplate: false
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
        api: 'setcrude',
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
    const colProps = { md: 6, xs: 12 };
    return (
      <Modal
        title={`${addOrEdit === '0' ? '填写' : '修改'}录入资料`}
        width="90%"
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
                {getFieldDecorator('crudeno', {
                  rules: [{ required: true, message: '请输入单号' }]
                })(
                  <Input placeholder="请输入单号" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="制单日期">
                {getFieldDecorator('billdate', {
                  initialValue: moment(),
                  rules: [{ required: true, message: '请选择制单日期' }]
                })(
                  <DatePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="客户">
                {getFieldDecorator('custname', {
                  rules: [{ required: true, message: '请选择客户' }]
                })(
                  <Input
                    readOnly
                    placeholder="请选择客户"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectCustomer: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="客户编号" style={{ display: 'none' }}>
                {getFieldDecorator('custno')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="加工工厂">
                {getFieldDecorator('factory')(
                  <Input
                    readOnly
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectFactory: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="工厂编号" style={{ display: 'none' }}>
                {getFieldDecorator('factoryno')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="缸号">
                {getFieldDecorator('suffixno')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="来胚数量">
                {getFieldDecorator('qty')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Row gutter={8}>
                <Col span={14}>
                  <Form.Item label="来胚长度">
                    {getFieldDecorator('extent')(
                      <Input />
                    )}
                  </Form.Item>
                </Col>
                <Col span={10}>
                  <Form.Item label="&nbsp;">
                    {getFieldDecorator('unit', {
                      initialValue: '码'
                    })(
                      <Select>
                        <Select.Option value="码">码</Select.Option>
                        <Select.Option value="米">米</Select.Option>
                      </Select>
                    )}
                  </Form.Item>
                </Col>
              </Row>
            </Col>
            <Col {...colProps}>
              <Form.Item label="胚布类别">
                {getFieldDecorator('class', {
                  initialValue: ''
                })(
                  <Select>
                    {classTypes.map(item => (
                      <Select.Option value={item}>{item}</Select.Option>
                    ))}
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="收货车牌">
                {getFieldDecorator('reveplate')(
                  <Input
                    readOnly
                    prefix={<Icon type="car" theme="outlined" />}
                    onClick={() => this.setState({ showSelectGecarplate: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="车型" style={{ display: 'none' }}>
                {getFieldDecorator('revecar')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="收货司机">
                {getFieldDecorator('driver')(
                  <Input
                    readOnly
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectWorker: true, selectWorkerType: '4' })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="收胚员">
                {getFieldDecorator('reveuser')(
                  <Input
                    readOnly
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectWorker: true, selectWorkerType: '0' })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="收胚日期">
                {getFieldDecorator('revedate')(
                  <DatePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="来胚备注">
                {getFieldDecorator('remark')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="规格">
                {getFieldDecorator('artname')(
                  <Input
                    readOnly
                    prefix={<Icon type="profile" theme="outlined" />}
                    onClick={() => this.setState({ showSelectModel: true })}
                  />
                )}
              </Form.Item>
            </Col>
          </Row>
          <div className={styles.divider}>
            <Divider orientation="left">规格信息</Divider>
          </div>
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="成品缩率">
                {getFieldDecorator('cpsl')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="成品门幅">
                {getFieldDecorator('cpmf')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="成品拉斜">
                {getFieldDecorator('stretch')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="成品斜纹">
                {getFieldDecorator('veins')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="加工工序">
                {getFieldDecorator('processtxt')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="加长要求">
                {getFieldDecorator('increq')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="是否分色">
                {getFieldDecorator('vision')(
                  <Select>
                    <Select.Option value="分色">分色</Select.Option>
                    <Select.Option value="不分色">不分色</Select.Option>
                  </Select>
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="并匹要求">
                {getFieldDecorator('attach')(
                  <Select>
                    <Select.Option value="0">不许并匹</Select.Option>
                    <Select.Option value="1">允许并匹</Select.Option>
                  </Select>
                )}
              </Form.Item>
            </Col>
          </Row>
        </Form>
        <SelectCustomer
          customerType="0"
          visible={this.state.showSelectCustomer}
          handleVisible={bool => this.setState({ showSelectCustomer: bool })}
          handleOk={(item) => {
            setFieldsValue({
              custno: item.billno,
              custname: item.title
            });
          }}
        />
        <SelectCustomer
          customerType="1"
          visible={this.state.showSelectFactory}
          handleVisible={bool => this.setState({ showSelectFactory: bool })}
          handleOk={(item) => {
            setFieldsValue({
              factoryno: item.billno,
              factory: item.title
            });
          }}
        />
        <SelectDepot
          visible={this.state.showSelectDepot}
          handleVisible={bool => this.setState({ showSelectDepot: bool })}
          handleOk={(item) => {
            setFieldsValue({
              depotcode: item.depotcode,
              depotname: item.depotname
            });
          }}
        />
        <SelectGecarplate
          visible={this.state.showSelectGecarplate}
          handleVisible={bool => this.setState({ showSelectGecarplate: bool })}
          handleOk={(item) => {
            setFieldsValue({
              reveplate: item.reveplate,
              revecar: item.revecar
            });
          }}
        />
        <SelectWorker
          visible={this.state.showSelectWorker}
          workerType={this.state.selectWorkerType}
          handleVisible={bool => this.setState({ showSelectWorker: bool })}
          handleOk={(items) => {
            const { worker } = items[0];
            if (this.state.selectWorkerType === '0') {
              setFieldsValue({
                reveuser: worker
              });
            } else if (this.state.selectWorkerType === '4') {
              setFieldsValue({
                driver: worker
              });
            }
          }}
        />
        <SelectModel
          visible={this.state.showSelectModel}
          handleVisible={bool => this.setState({ showSelectModel: bool })}
          handleOk={(item) => {
            setFieldsValue({
              artname: item.artname,
              cpsl: item.cpsl,
              cpmf: item.cpmf,
              stretch: item.stretch,
              veins: item.veins,
              processtxt: item.processtxt,
              increq: item.increq,
              attach: item.attach
            });
          }}
        />
      </Modal>
    );
  }
}

AddPanel.propTypes = {
  addOrEdit: PropTypes.oneOf(['0', '1']) // 0新增 1编辑
};

AddPanel.defaultProps = {
  addOrEdit: '0'
};


export default AddPanel;
