/**
 * 图片文件转换为base64
 * @param {Object} img
 *   图片文件对象
 */
export function getBase64(img, callback) {
  const reader = new FileReader();
  reader.addEventListener('load', () => callback(reader.result));
  reader.readAsDataURL(img);
}

/**
 * canvas图片对象转换为base64
 * @param {Object} canvasImg
 * @param {Object} quality
 *  图片质量等级
 */
export function canvasToBase64(canvasImg, quality) {
  return canvasImg.toDataURL('image/jpeg', quality);
}

/**
 * 改变图片的质量
 * @param {Object} options
 *  options: {
 *    width: number,
 *    height: number,
 *    quality: number,
 *  }
 */
export function imageCompress(path, options = {}) {
  /* eslint-disable func-names, prefer-promise-reject-errors */
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.src = path;
    img.onerror = function () {
      reject('图片加载失败');
    };
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

      const base64 = canvasToBase64(canvas, quality);
      resolve(base64);
    };
  });
}
