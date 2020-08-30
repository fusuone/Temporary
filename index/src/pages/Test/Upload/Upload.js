import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Spin, Card, Form } from 'antd';

import ImagePicker from '@/cps/ImagePicker';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
@Form.create()
class Index extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    return (
      <div>
        <Spin spinning={false}>
          <Card bordered={false}>
            <ImagePicker />
          </Card>
        </Spin>
      </div>
    );
  }
}

export default Index;
