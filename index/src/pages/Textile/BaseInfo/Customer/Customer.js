import React, { PureComponent } from 'react';
import { Row, Col } from 'antd';

import CustomerList from './CustomerList';
import CustomerAdd from './CustomerAdd';

class Customer extends PureComponent {
  onRefresh = (type) => {
    this.customerListContext.onRefresh(type);
  }

  togglePage = (type, item) => {
    this.customerAddContext.togglePage(type, item);
  }

  render() {
    return (
      <Row gutter={24}>
        <Col lg={7} md={24} style={{ marginBottom: '24px' }}>
          <CustomerList
            getContext={c => this.customerListContext = c}
            togglePage={this.togglePage}
          />
        </Col>
        <Col lg={17} md={24} style={{ marginBottom: '24px' }}>
          <CustomerAdd
            getContext={c => this.customerAddContext = c}
            onRefresh={this.onRefresh}
          />
        </Col>
      </Row>
    );
  }
}

export default Customer;
