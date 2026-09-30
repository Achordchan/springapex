/**
 * 新闻详情页侧栏目录：高亮读者当前所在的章节。
 *
 * 章节以「标题已滚过视口上方约三分之一」为准；目录本身太长在侧栏里滚动时，
 * 把当前项滚进目录可见区，不带动整页。
 */
(function () {
  'use strict';

  const toc = document.querySelector('[data-news-toc]');
  if (!toc || !('IntersectionObserver' in window)) return;

  const links = Array.from(toc.querySelectorAll('a[href^="#"]'));
  const headings = links
    .map((link) => document.getElementById(decodeURIComponent(link.getAttribute('href').slice(1))))
    .filter(Boolean);
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

  // 标题进出视口时重新判定，比监听每一次滚动省。
  const observer = new IntersectionObserver(update, { rootMargin: '0px 0px -66% 0px' });
  headings.forEach((heading) => observer.observe(heading));
  update();
})();
