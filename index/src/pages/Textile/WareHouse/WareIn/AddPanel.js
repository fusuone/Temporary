import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, message, Form, Input, DatePicker, Icon } from 'antd';
import router from 'umi/router';
import moment from 'moment';

import http from '@/utils/http';

import SelectDepot from '@/cps/SelectComponents/SelectDepot';
import SelectWorker from '@/cps/SelectComponents/SelectWorker';
import SelectGecarplate from '@/cps/SelectComponents/SelectGecarplate';

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
      showSelectWorker: false,
      showSelectGecarplate: false
    };

    props.getContext && props.getContext(this);
  }

  resetForm = () => {
    this.props.form.resetFields();
  }

  // 提交
  handleSubmit = (e) => {
    e.preventDefault();
    const {
      currentUser,
      getTableList,
      resetTableList,
      form: {
        validateFieldsAndScroll
      }
    } = this.props;
    const orderBody = getTableList();
    if (orderBody.length <= 0) {
      message.info('请先选择入库细码！');
      return;
    }
    validateFieldsAndScroll((err, values) => {
      if (err) return;
      this.setState({ submitting: true });
      const orderHead = {
        admin: currentUser.admin,
        usercode: currentUser.billno,
        username: currentUser.username,
        ...values,
        movedate: values.movedate.format('YYYY-MM-DD H:m:s'),
        billdate: values.billdate.format('YYYY-MM-DD H:m:s')
      };
      http({
        method: 'post',
        api: 'setwaretrackorder',
        data: {
          head: orderHead,
          body: orderBody
        }
      }).then(({ status, msg }) => {
        if (status === '0') {
          message.success(
            <span>处理成功，
              <a onClick={() => router.push('/textile/warehouse/ware-list')}>点击查看</a>
            </span>
          );
          this.resetForm();
          resetTableList();
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
      <Card title="填写入库信息" bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="制单日期">
                {getFieldDecorator('billdate', {
                  initialValue: moment(),
                  rules: [{ required: true, message: '请选择交货日期' }]
                })(
                  <DatePicker style={{ width: '100%' }} />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="仓库">
                {getFieldDecorator('depotname', {
                  rules: [{ required: true, message: '请选择仓库' }]
                })(
                  <Input
                    readOnly
                    placeholder="请选择仓库"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectDepot: true })}
                  />
                )}
              </Form.Item>
              <Form.Item label="仓库编号" style={{ display: 'none' }}>
                {getFieldDecorator('depotcode')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="转仓卷数">
                {getFieldDecorator('qty')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="转仓码数">
                {getFieldDecorator('bigness')(
                  <Input />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="送货人">
                {getFieldDecorator('workername')(
                  <Input
                    readOnly
                    placeholder="请选择送货人"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectWorker: true, selectWorkerType: '4' })}
                  />
                )}
              </Form.Item>
              <Form.Item label="送货人编号" style={{ display: 'none' }}>
                {getFieldDecorator('workercode')(<Input />)}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="仓管员">
                {getFieldDecorator('operator')(
                  <Input
                    readOnly
                    placeholder="请选择仓管员"
                    prefix={<Icon type="user" theme="outlined" />}
                    onClick={() => this.setState({ showSelectWorker: true, selectWorkerType: '3' })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="送货车牌">
                {getFieldDecorator('carplate')(
                  <Input
                    readOnly
                    placeholder="请选择送货车牌"
                    prefix={<Icon type="car" theme="outlined" />}
                    onClick={() => this.setState({ showSelectGecarplate: true })}
                  />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="转仓日期">
                {getFieldDecorator('movedate')(
                  <DatePicker style={{ width: '100%' }} />
                )}
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
          <Row>
            <Button type="primary" size="large" onClick={this.handleSubmit} loading={submitting}>提交</Button>
          </Row>
        </Form>
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
              carplate: item.reveplate
            });
          }}
        />
        <SelectWorker
          visible={this.state.showSelectWorker}
          workerType={this.state.selectWorkerType}
          handleVisible={bool => this.setState({ showSelectWorker: bool })}
          handleOk={(items) => {
            const { billno, worker } = items[0];
            if (this.state.selectWorkerType === '3') {
              setFieldsValue({
                operator: worker
              });
            } else if (this.state.selectWorkerType === '4') {
              setFieldsValue({
                workercode: billno,
                workername: worker
              });
            }
          }}
        />
      </Card>
    );
  }
}

export default AddPanel;
