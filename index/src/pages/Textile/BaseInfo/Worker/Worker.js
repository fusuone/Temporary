import React, { PureComponent } from 'react';
import { Row, Col } from 'antd';

import WorkerList from './WorkerList';
import WorkerAdd from './WorkerAdd';

class Worker extends PureComponent {
  onRefresh = (type) => {
    this.customerListContext.onRefresh(type);
  }

  togglePage = (type, item) => {
    this.customerAddContext.togglePage(type, item);
  }

  render() {
    return (
      <Row gutter={24}>
        <Col style={{ marginBottom: '24px' }}>
          <WorkerAdd
            getContext={c => this.customerAddContext = c}
            onRefresh={this.onRefresh}
          />
        </Col>
        <Col style={{ marginBottom: '24px' }}>
          <WorkerList
            getContext={c => this.customerListContext = c}
            togglePage={this.togglePage}
          />
        </Col>
      </Row>
    );
  }
}

export default Worker;
