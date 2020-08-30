handleUpload = async (params) => {
  const { action, data, file, filename, onError, onProgress, onSuccess } = params;
  console.log(file);

  if (!this.checkFileExpandedName(file.name)) {
    message.error('只支持上传【jpg、png、gif】格式图片！');
    return false;
  }
  if (!this.checkFileSize(file.size)) {
    message.error(`上传文件大小不能超过${this.props.fixSize}M！`);
    return false;
  }

  // 进行压缩
  console.log('压缩前大小：', file.size);
  const quality = this.switchQuality(file.size);
  let newFile;
  try {
    const base64 = await imageCompress(file, { quality });
    newFile = dataURLToBlob(base64, file.name);
    newFile.uid = file.uid;
    console.log('压缩后大小：', newFile.size);
  } catch (error) {
    message.error('图片处理错误！');
    return false;
  }

  console.log(fileToObject(newFile));
  return;
  const formData = new FormData();
  if (data) {
    Object.keys(data).map(key => formData.append(key, data[key]));
  }
  formData.append(filename, file);

  try {
    const response = http({
      method: 'post',
      url: action,
      data: formData,
      onUploadProgress: ({ total, loaded }) => {
        onProgress({ percent: Math.round(loaded / total * 100).toFixed(2) }, file);
      },
      timeout: 0,
      extraOpts: {
        isNotify: false
      }
    });
    onSuccess(response, file);
  } catch (error) {
    onError(error);
  }

  return {
    abort() {
      console.log('upload progress is aborted.');
    }
  };
}