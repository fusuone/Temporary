import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Row, Col, Button, Card, Form, Input, Icon } from 'antd';

import SelectCrude from '@/cps/SelectComponents/SelectCrude';

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
      showSelectCrude: false,
      currentCrudeItem: null
    };

    props.getContext && props.getContext(this);
  }

  getCurrentCrudeItem = () => {
    return this.state.currentCrudeItem;
  }

  resetForm = () => {
    this.props.form.resetFields();
  }

  render() {
    const {
      form: { getFieldDecorator, setFieldsValue }
    } = this.props;
    const colProps = { md: 6, xs: 12 };
    return (
      <Card title="码单信息" bordered={false}>
        <Form layout="vertical">
          <Row gutter={24}>
            <Col {...colProps}>
              <Form.Item label="客户">
                {getFieldDecorator('custname')(
                  <Input
                    disabled
                    prefix={<Icon type="user" theme="outlined" />}
                  />
                )}
              </Form.Item>
              <Form.Item label="客户编号" style={{ display: 'none' }}>
                {getFieldDecorator('custno')(
                  <Input disabled />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="单号">
                {getFieldDecorator('serialno')(
                  <Input disabled />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="制单日期">
                {getFieldDecorator('billdate')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="缸号">
                {getFieldDecorator('suffixno')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="规格">
                {getFieldDecorator('model')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="颜色">
                {getFieldDecorator('color')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="来胚数量">
                {getFieldDecorator('qty')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="来胚数量">
                {getFieldDecorator('qty')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Row gutter={8}>
                <Col span={14}>
                  <Form.Item label="来胚长度">
                    {getFieldDecorator('extent')(
                      <Input readOnly />
                    )}
                  </Form.Item>
                </Col>
                <Col span={10}>
                  <Form.Item label="&nbsp;">
                    {getFieldDecorator('unit')(
                      <Input readOnly placeholder="单位" />
                    )}
                  </Form.Item>
                </Col>
              </Row>
            </Col>
            <Col {...colProps}>
              <Form.Item label="胚布类别">
                {getFieldDecorator('class')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="成品门幅">
                {getFieldDecorator('cpmf')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="成品缩率">
                {getFieldDecorator('cpsl')(
                  <Input readOnly suffix="%" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="损耗">
                {getFieldDecorator('loss')(
                  <Input readOnly suffix="%" />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="经向">
                {getFieldDecorator('vertical')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="纬向">
                {getFieldDecorator('weft')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col {...colProps}>
              <Form.Item label="收胚员">
                {getFieldDecorator('reveuser')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
            <Col sm={12} xs={24}>
              <Form.Item label="来胚备注">
                {getFieldDecorator('processremark')(
                  <Input readOnly />
                )}
              </Form.Item>
            </Col>
          </Row>
          <Row>
            <Button type="primary" onClick={() => this.setState({ showSelectCrude: true })}>选择胚布</Button>
          </Row>
        </Form>
        <SelectCrude
          visible={this.state.showSelectCrude}
          isAutoFetchData
          handleVisible={bool => this.setState({ showSelectCrude: bool })}
          handleOk={(items) => {
            const item = items[0];
            this.setState(
              { currentCrudeItem: item },
              () => this.props.fetchTrackList()
            );
            setFieldsValue({
              custno: item.custno,
              custname: item.custname,
              serialno: item.crudeno,
              billdate: item.billdate,
              suffixno: item.suffixno,
              model: item.model,
              color: item.color,
              extent: item.extent,
              unit: item.unit,
              class: item.class,
              cpmf: item.cpmf,
              cpsl: item.cpsl,
              loss: item.loss,
              vertical: item.vertical,
              weft: item.weft,
              processremark: item.processremark
            });
          }}
        />
      </Card>
    );
  }
}

export default AddPanel;
