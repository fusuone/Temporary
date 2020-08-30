import React, { PureComponent } from 'react';
import { Select, Spin } from 'antd';
import { connect } from 'dva';
import styles from './GeographicView.less';

const { Option } = Select;

const nullSlectItem = {
  label: '',
  key: ''
};

@connect(({ geographic, loading }) => {
  const { province, city } = geographic;
  return {
    province,
    city,
    isLoading: !!loading.effects['geographic/get']
  };
})
class GeographicView extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      selectProvinceId: null
    };
  }

  componentDidMount = () => {
    const { dispatch, onLoaded } = this.props;
    dispatch({
      type: 'geographic/get'
    }).then((status) => {
      if (status === 'ok') {
        const { province, city } = this.props;
        onLoaded && onLoaded(province, city);
      }
    });
  };

  getProvinceOption() {
    const { province } = this.props;
    return this.getOption(province);
  }

  getCityOption = () => {
    const { city } = this.props;
    const { selectProvinceId } = this.state;
    return this.getOption(city[selectProvinceId]);
  };

  getOption = (list) => {
    if (!list || list.length < 1) {
      return (
        <Option key={0} value={0} disabled>
          没有找到选项
        </Option>
      );
    }
    return list.map(item => (
      <Option key={item.id} value={item.id}>
        {item.name}
      </Option>
    ));
  };

  selectProvinceItem = (item) => {
    const { onChange } = this.props;
    this.setState({ selectProvinceId: item.key });
    onChange({
      province: item,
      city: nullSlectItem
    });
  };

  selectCityItem = (item) => {
    const { value, onChange } = this.props;
    onChange({
      province: value.province,
      city: item
    });
  };

  conversionObject() {
    const { value } = this.props;
    if (!value) {
      return {
        province: nullSlectItem,
        city: nullSlectItem
      };
    }
    const { province, city } = value;
    this.setState({ selectProvinceId: province.key });
    return {
      province: province || nullSlectItem,
      city: city || nullSlectItem
    };
  }

  render() {
    const { province, city } = this.conversionObject();
    const { isLoading } = this.props;
    return (
      <Spin spinning={isLoading} wrapperClassName={styles.row}>
        <Select
          className={styles.item}
          value={province}
          labelInValue
          onSelect={this.selectProvinceItem}
        >
          {this.getProvinceOption()}
        </Select>
        <Select
          className={styles.item}
          value={city}
          labelInValue
          onSelect={this.selectCityItem}
        >
          {this.getCityOption()}
        </Select>
      </Spin>
    );
  }
}

export default GeographicView;
