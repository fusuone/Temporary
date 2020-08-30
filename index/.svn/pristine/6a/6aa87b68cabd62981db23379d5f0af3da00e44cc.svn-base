// 图片浏览
/* eslint-disable react/button-has-type, func-names, jsx-a11y/anchor-has-content */

import React, { Component } from 'react';
import Photoswipe from 'photoswipe';
import PhotoswipeUIDefault from 'photoswipe/dist/photoswipe-ui-default';
import './styles.less';

// const testImages = [{
//   src: 'https://img3.doubanio.com/view/photo/l/public/p1240215930.webp',
//   w: 0,
//   h: 0
// }, {
//   src: 'https://img3.doubanio.com/view/photo/m/public/p2367928231.webp',
//   w: 0,
//   h: 0
// }];

export default class PhotoSwipe extends Component {
  componentWillUnmount() {
    this.photoSwipe && this.photoSwipe.close();
  }

  open(images = [], options) {
    if (images.length <= 0) return;

    options = {
      history: false,
      closeOnScroll: false,
      ...options
    };
    this.photoSwipe = new Photoswipe(this.pswpElement, PhotoswipeUIDefault, images, options);
    this.photoSwipe.listen('gettingData', (idx, item) => {
      const that = this;
      const _item = item;
      if (item.w < 1 || item.h < 1) { // unknown size
        const img = new Image();
        img.onload = function () { // will get size after load
          _item.w = this.width; // set image width
          _item.h = this.height; // set image height
          that.photoSwipe.invalidateCurrItems(); // reinit Items
          that.photoSwipe.updateSize(true); // reinit Items
        };
        img.src = item.src; // let's download image
      }
    });
    this.photoSwipe.init();
  }

  render() {
    return (
      <div id="gallery" className="pswp" tabIndex="-1" aria-hidden="true" ref={node => this.pswpElement = node}>
        <div className="pswp__bg" />
        <div className="pswp__scroll-wrap">
          <div className="pswp__container">
            <div className="pswp__item" />
            <div className="pswp__item" />
            <div className="pswp__item" />
          </div>
          <div className="pswp__ui pswp__ui--hidden">
            <div className="pswp__top-bar">
              <div className="pswp__counter" />
              <button className="pswp__button pswp__button--close" title="Close (Esc)" />
              {/* <button className="pswp__button pswp__button--share" title="Share" /> */}
              <button className="pswp__button pswp__button--fs" title="Toggle fullscreen" />
              <button className="pswp__button pswp__button--zoom" title="Zoom in/out" />
              <div className="pswp__preloader">
                <div className="pswp__preloader__icn">
                  <div className="pswp__preloader__cut">
                    <div className="pswp__preloader__donut" />
                  </div>
                </div>
              </div>
            </div>
            <div className="pswp__loading-indicator"><div className="pswp__loading-indicator__line" /></div>
            <div className="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
              <div className="pswp__share-tooltip">
                <a href="#" className="pswp__share--facebook" />
                <a href="#" className="pswp__share--twitter" />
                <a href="#" className="pswp__share--pinterest" />
                <a href="#" download className="pswp__share--download" />
              </div>
            </div>
            <button className="pswp__button pswp__button--arrow--left" title="Previous (arrow left)" />
            <button className="pswp__button pswp__button--arrow--right" title="Next (arrow right)" />
            <div className="pswp__caption">
              <div className="pswp__caption__center" />
            </div>
          </div>
        </div>
      </div>
    );
  }
}
