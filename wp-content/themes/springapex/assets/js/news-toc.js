/**
 * 新闻详情页侧栏目录：高亮读者当前所在的章节。
 *
 * 章节以「标题已滚过视口上方三分之一处」为准，滚动时按帧节流判定；目录本身太长在侧栏里滚动时，
 * 把当前项滚进目录可见区，不带动整页。
 */
(function () {
  'use strict';

  const toc = document.querySelector('[data-news-toc]');
  if (!toc) return;

  // 目录项和正文标题一一配对，找不到标题的项直接跳过，序号不会错位。
  const links = [];
  const headings = [];
  toc.querySelectorAll('a[href^="#"]').forEach((link) => {
    let heading = null;
    try {
      heading = document.getElementById(decodeURIComponent(link.getAttribute('href').slice(1)));
    } catch (error) {
      heading = null;
    }
    if (heading) {
      links.push(link);
      headings.push(heading);
    }
  });
  if (!headings.length) return;

  const list = toc.querySelector('.sa-news-toc__list');
  let current = null;

  const setCurrent = (index) => {
    const link = index >= 0 ? links[index] : null;
    if (link === current) return;
    if (current) current.removeAttribute('aria-current');
    current = link;
    if (!link) return;
    link.setAttribute('aria-current', 'true');
    if (list && list.scrollHeight > list.clientHeight) {
      const top = link.offsetTop - list.offsetTop;
      if (top < list.scrollTop || top + link.offsetHeight > list.scrollTop + list.clientHeight) {
        list.scrollTop = top - list.clientHeight / 2;
      }
    }
  };

  const update = () => {
    const line = window.innerHeight / 3;
    let index = -1;
    headings.forEach((heading, i) => {
      if (heading.getBoundingClientRect().top <= line) index = i;
    });
    setCurrent(index);
  };

  // 滚动和窗口尺寸变化时按帧节流重新判定；判定线随视口高度走，窗口变了也准。
  let pending = false;
  const schedule = () => {
    if (pending) return;
    pending = true;
    window.requestAnimationFrame(() => {
      pending = false;
      update();
    });
  };
  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', schedule);
  update();
})();
