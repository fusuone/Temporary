import React, { Component, Fragment } from 'react';
import propTypes from 'prop-types';
import { Upload, Icon, message } from 'antd';

import Api from '@/common/api';
import PhotoSwipe from '../PhotoSwipe';

import { dataURLToBlob, blobToDataURL, imageCompress } from './utils';
import './styles.less';

class ImagePicker extends Component {
  constructor(props) {
    super(props);
    this.state = {
      fileList: this.getInitialValue(props) || []
    };
  }

  componentWillReceiveProps(nextProps) {
    const fileList = this.getInitialValue(nextProps);
    if (fileList !== this.state.fileList) {
      this.setState({ fileList: fileList || [] });
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

  // 过滤上传失败的文件
  filterUploadFailFile = (list, tragetItem) => {
    return list.filter(file => file.uid !== tragetItem.uid);
  }

  getInitialValue = (props) => {
    let field = 'initialValue';
    if (props['data-__field']) {
      field = 'value';
    }
    return props[field];
  }

  tiggerChange = () => {
    const { onChange } = this.props;
    onChange && onChange(this.state.fileList);
  }

  handlePreview = (e) => {
    // console.log(e);
    const items = [];
    let startIndex = 0;
    this.state.fileList.forEach((item, index) => {
      if (item.status === 'done') {
        if (e.uid === item.uid) {
          startIndex = index;
        }
        items.push({ src: item.url, w: 0, h: 0 });
      }
    });
    this.photoSwipeRef.open(items, { index: startIndex });
  }

  // 关闭自动上传
  offAutoUpload = (file, cb) => {
    blobToDataURL(file, (base64) => {
      this.setState(prevState => ({
        fileList: [
          ...prevState.fileList,
          Object.assign({}, file, { thumbUrl: base64, url: base64, status: 'done' })
        ]
      }), () => {
        cb();
        this.tiggerChange();
      });
    });
  }

  handleBeforeUpload = (file, fileList) => {
    return new Promise((resolve, reject) => {
      const { multiple, maxFileCount, maxFileSize, isCompress } = this.props;

      if (multiple && fileList.length > (maxFileCount - this.state.fileList.length)) {
        message.destroy();
        message.error(`图片数量不能超过${maxFileCount}张！`);
        reject();
        return;
      }
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

      if (isCompress) {
        // console.log('压缩前大小：', file.size);
        const quality = this.switchQuality(file.size);
        imageCompress(file, { quality }).then((base64) => {
          const newFile = dataURLToBlob(base64, file.name);
          newFile.uid = file.uid;
          // console.log('压缩后大小：', newFile.size);
          if (!this.props.isAutoUpload) {
            this.offAutoUpload(newFile, () => reject());
          } else {
            resolve(newFile);
          }
        }).catch(() => {
          message.error('图片处理错误！');
          reject();
        });
      } else {
        if (!this.props.isAutoUpload) { /* eslint-disable-line */
          this.offAutoUpload(file, () => reject());
        } else {
          resolve();
        }
      }
    });
  }

  // 如果 handleBeforeUpload 返回 reject 则不会执行这个函数
  handleChange = (e) => {
    // console.log(e);
    let isDone = false;
    let isRemov = false;
    let fileList = e.fileList;
    const fileStatus = e.file.status;

    if (fileStatus === 'uploading') {
      // console.log('上传中');
    } else if (fileStatus === 'done') {
      // console.log('上传完成');
      const { response } = e.file;
      if (!response) {
        message.error('上传失败，服务器未响应！');
        fileList = this.filterUploadFailFile(e.fileList, e.file);
      } else if (response.status === '0') {
        isDone = true;
        fileList = fileList.map((file) => {
          if (file.uid === e.file.uid) {
            return {
              uid: e.file.uid,
              status: 'done',
              url: response.data.url
            };
          }
          return file;
        });
      } else {
        message.error(response.msg);
        fileList = this.filterUploadFailFile(e.fileList, e.file);
      }
    } else if (fileStatus === 'error') {
      // console.log('上传失败');
      message.error('文件由于未知原因上传失败！');
      fileList = this.filterUploadFailFile(e.fileList, e.file);
    } else if (fileStatus === 'removed') {
      // console.log('删除文件');
      isRemov = true;
    } else {
      // console.log('beforeUpload return false');
      return;
    }
    this.setState({ fileList }, () => {
      (isDone || isRemov) && this.tiggerChange(); // 只当上传成功或删除文件才触发 Change，防止多余
    });
  }

  render() {
    const { fileList } = this.state;
    const { multiple, maxFileCount, disabled } = this.props;
    return (
      <Fragment>
        <Upload
          action={Api['uploadimage']}
          listType="picture-card"
          accept="image/jpg,image/jpeg,image/png,image/bmp" // https://blog.csdn.net/wyk304443164/article/details/71216077
          disabled={disabled}
          multiple={multiple}
          fileList={fileList}
          onChange={this.handleChange}
          onPreview={this.handlePreview}
          beforeUpload={this.handleBeforeUpload}
          showUploadList={{
            showRemoveIcon: !disabled
          }}
        >
          {disabled || fileList.length >= maxFileCount ?
            null
            :
            <div>
              <Icon type="plus" />
              <div className="ant-upload-text">上传图片</div>
            </div>
          }
        </Upload>
        <PhotoSwipe ref={ref => this.photoSwipeRef = ref} />
      </Fragment>
    );
  }
}

ImagePicker.propTypes = {
  maxFileSize: propTypes.number,
  maxFileCount: propTypes.number,
  isCompress: propTypes.bool,
  multiple: propTypes.bool,
  isAutoUpload: propTypes.bool,
  disabled: propTypes.bool,
  onChange: propTypes.func
};

ImagePicker.defaultProps = {
  maxFileSize: 6, // 文件规定大小，单位mb
  maxFileCount: 3, // 最多3张
  isCompress: true, // 是否进行压缩
  multiple: true, // 可选择多张图片
  isAutoUpload: true, // 是否选择图片后立即上传
  disabled: false,
  onChange: () => null
};

export default ImagePicker;
