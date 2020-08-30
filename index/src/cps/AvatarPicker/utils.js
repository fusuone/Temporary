// 参考 https://www.cnblogs.com/jyuf/p/7251591.html

// Blob对象转DataURL
export function blobToDataURL(file, callback) {
  const reader = new FileReader();
  reader.addEventListener('load', () => callback(reader.result));
  reader.readAsDataURL(file);
}

// Canvas转DataURL
export function canvasToDataURL(canvas, quality) {
  return canvas.toDataURL('image/jpeg', quality);
}

// DataURL转Blob对象
export function dataURLToBlob(dataURL, fileName) {
  const arr = dataURL.split(',');
  const mime = arr[0].match(/:(.*?);/)[1];
  const bstr = atob(arr[1]);
  let n = bstr.length;
  const u8arr = new Uint8Array(n);
  while (n--) { /* eslint-disable-line no-plusplus */
    u8arr[n] = bstr.charCodeAt(n);
  }
  return new File([u8arr], fileName || new Date().getTime(), { type: mime });
}

/**
 * 压缩图片
 * @param {Object} file
 *  图片文件对象
 * @param {Object} options
 *  {
 *    width: number,
 *    height: number,
 *    quality: number,
 *  }
 */
export function imageCompress(file, options = {}) {
  /* eslint-disable func-names, prefer-promise-reject-errors */
  return new Promise((resolve, reject) => {
    const img = new Image();
    let imgURL;
    if (typeof file === 'object') {
      const URL = window.URL || window.webkitURL;
      imgURL = URL.createObjectURL(file);
    } else {
      imgURL = file;
    }
    img.src = imgURL;
    img.onerror = error => reject(error);
    img.onload = function () {
      const that = this;

      // 默认按比例压缩
      let w = that.width;
      let h = that.height;
      const scale = w / h;

      // options的缩放大小
      w = options.width || w;
      h = options.height || (w / scale);

      // 默认图片质量为0.7，quality值越小所绘制出的图像越模糊
      let quality = 0.7;

      // 生成canvas
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      // 创建属性节点
      const anw = document.createAttribute('width');
      anw.nodeValue = w;
      const anh = document.createAttribute('height');
      anh.nodeValue = h;
      canvas.setAttributeNode(anw);
      canvas.setAttributeNode(anh);
      ctx.drawImage(that, 0, 0, w, h);

      // 图像质量
      if (options.quality && options.quality <= 1 && options.quality > 0) {
        quality = options.quality;
      }

      const base64 = canvasToDataURL(canvas, quality);
      resolve(base64);
    };
  });
}
