import React, { PureComponent, Fragment } from 'react';
import propTypes from 'prop-types';
import { Upload, Slider, message, Button, Modal } from 'antd';
import AvatarEditor from 'react-avatar-editor';

import http from '@/utils/http';
import Api from '@/common/api';

import { dataURLToBlob, blobToDataURL, imageCompress } from './utils';
import styles from './styles.less';

class AvatarPicker extends PureComponent {
  constructor(props) {
    super(props);
    this.defaultScale = 1.2;
    this.state = {
      avatar: this.getInitialValue(props), // 头像资源
      cacheAvatar: '', // 缓存的头像资源，用于裁剪
      scale: this.defaultScale, // 裁剪图片的缩放级别
      avatarEditorVisible: false,
      loading: false
    };
  }

  componentWillReceiveProps(nextProps) {
    const avatar = this.getInitialValue(nextProps);
    if (avatar !== this.state.avatar) {
      this.setState({ avatar });
    }
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
      q = 0.92;
    }
    return q;
  }

  // 检查文件扩展名
  checkFileExpandedName = (name) => {
    return /\.(jpe?g|png|gif)$/i.test(name);
  }

  // 检查文件大小
  checkFileSize = (size) => {
    const fileSize = size / 1024 / 1024;
    const isLt = fileSize <= this.props.maxFileSize;
    return isLt;
  }

  getInitialValue = (props) => {
    let field = 'initialValue';
    if (props['data-__field']) {
      field = 'value';
    }
    return props[field];
  }

  handleBeforeUpload = (file) => {
    return new Promise((resolve, reject) => {
      const { type, maxFileSize } = this.props;

      if (!this.checkFileExpandedName(file.name)) {
        message.error('只支持上传【jpg、png、gif】格式图片！');
        reject();
        return;
      }
      if (!this.checkFileSize(file.size)) {
        message.error(`上传文件大小不能超过${maxFileSize}M！`);
        reject();
        return;
      }

      if (type === 'cropping') {
        blobToDataURL(file, (base64) => {
          this.setState({
            cacheAvatar: base64,
            avatarEditorVisible: true
          });
          reject();
        });
      } else if (type === 'compress') {
        const quality = this.switchQuality(file.size);
        imageCompress(file, { quality }).then((base64) => {
          this.setState({ avatar: base64 }, this.handleUpload);
          reject();
        }).catch(() => {
          message.error('图片处理错误！');
          reject();
        });
      } else if (type === 'original') {
        blobToDataURL(file, (base64) => {
          this.setState({ avatar: base64 }, this.handleUpload);
          reject();
        });
      } else {
        message.error('不匹配！');
        reject();
      }
    });
  }

  handleOk = () => {
    if (!this.avatarEditorRef) return;
    const dataURL = this.avatarEditorRef.getImage().toDataURL();
    const quality = this.switchQuality(dataURLToBlob(dataURL).size);
    imageCompress(dataURL, { quality }).then((base64) => {
      this.setState({
        avatar: base64,
        avatarEditorVisible: false
      }, this.handleUpload);
    }).catch(() => {
      message.error('图片处理错误！');
    });
  }

  handleUpload = async () => {
    if (!this.props.isAutoUpload) {
      this.props.onChange(this.state.avatar);
      return;
    }

    this.setState({ loading: true });
    const data = {}; // 提交的参数
    const { avatar } = this.state;
    const formData = new FormData();
    const file = dataURLToBlob(avatar);

    if (data) {
      Object.keys(data).map(key => formData.append(key, data[key]));
    }
    formData.append('file', file);

    try {
      const response = await http({
        method: 'post',
        url: Api['uploadimage'],
        data: formData,
        timeout: 0
      });
      if (response.status !== '0') {
        message.error(response.msg);
        this.setState({ avatar: this.getInitialValue(this.props) }); // 如果没有上传失败则恢复原头像
      } else {
        this.props.onChange(response.data.url);
      }
    } catch (error) {
      this.setState({ avatar: this.getInitialValue(this.props) });
    }
    this.setState({ loading: false });
  }

  renderAvatarEditorModal() {
    const { avatarEditorVisible, cacheAvatar, scale } = this.state;
    return (this.props.type === 'cropping' && (
      <Modal
        title="裁剪图片"
        width="300px"
        maskClosable={false}
        visible={avatarEditorVisible}
        onOk={this.handleOk}
        onCancel={() => this.setState({ avatarEditorVisible: false })}
        afterClose={() => this.setState({ scale: this.defaultScale, cacheAvatar: '' })}
      >
        <AvatarEditor
          ref={ref => this.avatarEditorRef = ref}
          style={{ width: '100%', height: '100%' }}
          image={cacheAvatar}
          width={300}
          height={300} // 宽高只作用于裁剪模式
          scale={scale}
          color={[255, 255, 255, 0.6]}
        />
        <Slider
          min={1}
          max={2}
          step={0.1}
          value={scale}
          defaultValue={this.defaultScale}
          onChange={value => this.setState({ scale: value })}
        />
      </Modal>
    ));
  }

  render() {
    const { loading, avatar } = this.state;
    const { disabled } = this.props;
    return (
      <Fragment>
        <div className={styles.avatar}>
          <img src={avatar} alt="头像" />
        </div>
        <Upload
          accept="image/jpg,image/jpeg,image/png,image/bmp" // https://blog.csdn.net/wyk304443164/article/details/71216077
          beforeUpload={this.handleBeforeUpload}
          showUploadList={false}
        >
          <div className={styles.button_view}>
            <Button icon={loading ? 'loading' : 'upload'} disabled={disabled || loading}>
              { loading ? '上传中' : '更换头像' }
            </Button>
          </div>
        </Upload>
        {this.renderAvatarEditorModal()}
      </Fragment>
    );
  }
}

AvatarPicker.propTypes = {
  type: propTypes.oneOf(['cropping', 'compress', 'original']),
  maxFileSize: propTypes.number,
  isAutoUpload: propTypes.bool,
  disabled: propTypes.bool,
  onChange: propTypes.func
};

AvatarPicker.defaultProps = {
  type: 'cropping', // cropping(裁剪) | compress(压缩) | original(原图)
  maxFileSize: 6, // 文件规定大小，单位mb
  isAutoUpload: true, // 是否选择图片后立即上传
  disabled: false,
  onChange: () => null
};

export default AvatarPicker;
