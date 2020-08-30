/**
 * 选取图片
 */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import AvatarEditor from 'react-avatar-editor';
import { Modal, Slider, Icon, message } from 'antd';

import FullScreenImage from '../FullScreenImage';
import { getBase64, canvasToBase64, imageCompress } from './utils';
import './style.less';

export default class extends Component {
  static propTypes = {
    type: PropTypes.oneOf(['cropping', 'compress', 'original']),
    isClearIcon: PropTypes.bool,
    onClear: PropTypes.func,
    onChange: PropTypes.func,
    onPreview: PropTypes.func,
  }

  static defaultProps = {
    type: 'compress', // cropping(裁剪) | compress(压缩) | original(原图)
    isClearIcon: true, // 是否显示删除按钮
    onClear: () => null,
    onChange: () => null,
    onPreview: () => null,
  }

  constructor(props) {
    super(props);
    this.defaultImageScale = 1.2;
    this.state = {
      source: props.source,
      isChange: false, // 是否有过选择过图片的操作
      imageScale: this.defaultImageScale, // 裁剪图片的缩放级别
      cropingImgURL: '', // 选择要裁剪的图片地址
      cropingImgVisible: false, // 显示裁剪图片弹层
    };
  }

  componentWillReceiveProps(nextProps) {
    // 如果没有选择过图片的操作，则一直可以从父组件更新
    // 作用：如果更改头像处，获取资料是异步，需要再次更新
    if (!this.state.isChange) {
      this.setState({ source: nextProps.source });
    }
  }

  // 预览图片
  onPreview = () => {
    const images = [{ src: this.state.source, w: 0, h: 0 }];
    this.fullScreenImageRef.open(images);
    this.props.onPreview();
  }

  // 清除选择的图片
  onClear = () => {
    this.setState({ source: '', isChange: true });
    this.props.onClear();
  }

  // 调起选择图片
  onChoose = () => {
    this.inputRef.click();
  }

  // 控制裁剪图片的缩放
  onImageScale = (value) => {
    this.setState({ imageScale: value });
  }

  // 点击裁剪的取消按钮
  onModalCancelBtn = () => {
    this.setState({ cropingImgVisible: false });
  }

  // 点击裁剪框的确认按钮
  onModalOkBtn = () => {
    const { cropingImgURL } = this.props;
    const { onChange } = this.props;
    if (!this.avatarEditorRef) return;
    // const canvas = this.avatarEditorRef.getImage();
    const canvasScaled = this.avatarEditorRef.getImageScaledToCanvas();
    const base64 = canvasToBase64(canvasScaled);
    this.setState({ cropingImgVisible: false, source: base64 });
    onChange(base64, cropingImgURL);
  }

  // 裁剪关闭回调
  onCloseModal = () => {
    if (this.state.imageScale !== this.defaultImageScale) {
      this.setState({ imageScale: this.defaultImageScale });
    }
  }

  // 选取了图片
  onChange = (e) => {
    const { type, onChange } = this.props;
    const { files } = e.target;
    let file;
    if (files && files.length > 0) file = files[0];

    if (!file) {
      // 是否选择了图片
      return;
    } else if (!this.verify(file)) {
      return;
    }

    this.setState({ isChange: true });

    // 获取window的URL工具，通过file生成目标url
    const URL = window.URL || window.webkitURL;
    const imgURL = URL.createObjectURL(file);

    // console.log('文件对象', file);
    // console.log('图片路径', imgURL);

    // 三个不同的类型
    if (type === 'cropping') {
      this.setState({
        cropingImgVisible: true,
        cropingImgURL: imgURL,
      });
    } else if (type === 'compress') {
      // 图片容量超过指定大小才进行压缩
      const quality = this.switchQuality(file.size);
      if (quality === 1) {
        this.setState({ source: imgURL });
        getBase64(file, base64 => onChange(base64, imgURL));
        return;
      }
      imageCompress(imgURL, { quality }).then((base64) => {
        this.setState({ source: base64 });
        onChange(base64, imgURL);
      });
    } else if (type === 'original') {
      this.setState({ source: imgURL });
      getBase64(file, base64 => onChange(base64, imgURL));
    }
  }

  // 图片规则验证
  verify = (file) => {
    const isJPG = file.type === 'image/jpeg' || file.type === 'image/jpg' || file.type === 'image/png';
    if (!isJPG) {
      message.error('请选择jpg或png格式的图片！');
      return false;
    }
    const isLt = file.size / 1024 / 1024 < 5;
    if (!isLt) {
      message.error('图片必须小于5MB！');
      return false;
    }
    return true;
  }

  // 根据图片大小压缩质量
  switchQuality = (size) => {
    const s = size / 1024 / 1024;
    let q;
    if (s > 0.5 && s < 1) {
      q = 0.7;
    } else if (s > 1 && s < 2) {
      q = 0.5;
    } else if (s > 2 && s < 3) {
      q = 0.4;
    } else if (s > 3 && s < 4) {
      q = 0.3;
    } else if (s > 4 && s < 5) {
      q = 0.2;
    } else if (s > 5) {
      q = 0.1;
    } else {
      q = 1;
    }
    return q;
  }

  renderCropingImgModal() {
    const { cropingImgURL, imageScale, cropingImgVisible } = this.state;
    const { type } = this.props;
    return (type === 'cropping' && (
      <Modal
        title="裁剪图片"
        cancelText="取消"
        okText="确认"
        width="300px"
        maskClosable={false}
        visible={cropingImgVisible}
        onOk={this.onModalOkBtn}
        onCancel={this.onModalCancelBtn}
        afterClose={this.onCloseModal}
      >
        <AvatarEditor
          ref={ref => this.avatarEditorRef = ref}
          style={{ width: '100%', height: '100%' }}
          image={cropingImgURL}
          width={300}
          height={300} // 宽高只作用于裁剪模式
          scale={imageScale}
          color={[255, 255, 255, 0.6]}
        />
        <Slider min={1} max={2} step={0.1} defaultValue={imageScale} value={imageScale} onChange={this.onImageScale} />
      </Modal>)
    );
  }

  render() {
    const { source } = this.state;
    const { style, isClearIcon } = this.props;
    return (
      <div className="choose-image-container" style={style}>
        {/* 选择图片 */}
        <div className="choose-image" style={{ display: source ? 'none' : 'block' }} onClick={this.onChoose}>
          <Icon type="plus" className="icon" />
          <input ref={ref => this.inputRef = ref} type="file" accept="image/*" onChange={this.onChange} />
        </div>
        {/* 展示图片 */}
        <div className="show-image" style={{ display: source ? 'block' : 'none' }}>
          <img className="image" src={source} alt="" />
          <div className="image-handel">
            <Icon className="icon" type="eye-o" onClick={this.onPreview} />
            {isClearIcon &&
              <Icon className="icon" type="delete" style={{ marginLeft: '10px' }} onClick={this.onClear} />
            }
          </div>
        </div>

        {/* 大图 */}
        <FullScreenImage ref={ref => this.fullScreenImageRef = ref} />

        {/* 裁剪图片弹框 */}
        {this.renderCropingImgModal()}
      </div>
    );
  }
}
