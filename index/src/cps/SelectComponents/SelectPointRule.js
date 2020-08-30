// 积分规则
// 注意 https://ant.design/components/form-cn/#this.props.form.getFieldDecorator(id,-options)
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'dva';
import { Select, message, Spin } from 'antd';

import http from '@/utils/http';

@connect(({
  user
}) => ({
  currentUser: user.currentUser
}))
class SelectPointRule extends PureComponent {
  constructor(props) {
    super(props);

    this.state = {
      loading: false,
      listData: {
        list: [],
        total: 0
      },
      reqParams: {
        page: 1,
        admin: props.currentUser.admin,
        ispaging: '2'
      }
    };
  }

  componentDidMount() {
    if (this.props.isSilentFetch) {
      this.getList();
    }
  }

  onChange = (value) => {
    const { listData } = this.state;
    const { onChange } = this.props;
    let items = [];
    if (Array.isArray(value)) {
      value.forEach((v1) => {
        listData.list.forEach((v2) => {
          v1.key === v2.id && items.push(v2);
        });
      });
    } else {
      items = listData.list.filter(v => v.id === value.key);
    }
    onChange && onChange(items);
  }

  // 展开下拉菜单的回调
  onDropdownVisibleChange = (isOpen) => {
    if (isOpen) {
      if (this.props.isAlwaysFetchData) {
        if (!this.state.loading) {
          this.setState({
            listData: {
              list: [],
              total: 0
            }
          }, () => this.getList());
        }
        return;
      }
      if (this.state.listData.list.length <= 0) { // 不会重复获取
        this.getList();
      }
    }
  }

  getList = async () => {
    const { loading, reqParams } = this.state;
    if (loading) return;
    this.setState({ loading: true });
    try {
      const { status, msg, data } = await http({
        method: 'get',
        api: 'getpointitemlist',
        params: reqParams
      });
      if (status === '0') {
        this.setState(prevState => ({
          loading: false,
          listData: {
            ...prevState.listData,
            list: data.list,
            total: Number(data.total)
          }
        }));
      } else {
        message.warn(msg);
        this.setState({ loading: false });
      }
    } catch (error) {
      this.setState({ loading: false });
    }
  }

  // 转换
  conversionObject() {
    const { value } = this.props;
    if (!value) {
      return [];
    }
    const data = value.map((item) => {
      return { key: item.id, label: item.rulename };
    });
    return data;
  }

  render() {
    const { loading, listData } = this.state;
    const { mode, ...others } = this.props;
    const valueProps = this.props['data-__field'] && { value: this.conversionObject() }; // 判断是否经过 From 包裹
    return (
      <Select
        mode={mode}
        {...others}
        labelInValue
        {...valueProps}
        notFoundContent={loading ? <Spin size="small" /> : null}
        onDropdownVisibleChange={this.onDropdownVisibleChange}
        onChange={this.onChange}
      >
        {listData.list.map(item => <Select.Option key={item.id}>{item.rulename}</Select.Option>)}
      </Select>
    );
  }
}

SelectPointRule.propTypes = {
  isSilentFetch: PropTypes.bool,
  isAlwaysFetchData: PropTypes.bool,
  mode: PropTypes.oneOf(['default', 'multiple', 'tags', 'combobox'])
};

SelectPointRule.defaultProps = {
  isSilentFetch: false, // 用于静默获取列表数据(只会触发一次)
  isAlwaysFetchData: false, // 当展开下拉菜单总是会重新获取数据
  mode: 'default'
};

export default SelectPointRule;
