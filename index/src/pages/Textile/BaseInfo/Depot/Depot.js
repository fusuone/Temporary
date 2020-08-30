import React, { PureComponent } from 'react';
import { Row, Col } from 'antd';

import DepotList from './DepotList';
import DepotAdd from './DepotAdd';

class Depot extends PureComponent {
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
          <DepotAdd
            getContext={c => this.customerAddContext = c}
            onRefresh={this.onRefresh}
          />
        </Col>
        <Col style={{ marginBottom: '24px' }}>
          <DepotList
            getContext={c => this.customerListContext = c}
            togglePage={this.togglePage}
          />
        </Col>
      </Row>
    );
  }
}

export default Depot;
