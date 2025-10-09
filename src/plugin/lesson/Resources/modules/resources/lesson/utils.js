import isEmpty from 'lodash/isEmpty'

import {NUMBERING_LITERAL, NUMBERING_NUMERIC} from '#/main/app/utils/numbering'

/**
 * Get the display numbering of a chapter.
 *
 * @param {string} type
 * @param {Array} chapters
 * @param {chapter.propTypes} chapter
 *
 * @return {string}
 */
function getNumbering(type, chapters, chapter) {
  function buildPath(chapters, chapter) {
    let chapterPath = []

    chapters.map((s, i) => {
      if (s.id === chapter.id) {
        chapterPath.push(i) // add current chapter to the path
      } else if (s.children && 0 !== s.children.length) {
        const subPath = buildPath(s.children, chapter)
        if (0 !== subPath.length) {
          chapterPath = chapterPath.concat([i], subPath)
        }
      }
    })

    return chapterPath
  }

  switch (type) {
    case NUMBERING_NUMERIC:
      return '' + buildPath(chapters, chapter)
        .map(i => i + 1)
        .join('.') + '.'

    case NUMBERING_LITERAL:
      return buildPath(chapters, chapter)
        .map(i => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[i])
        .join('.') + '.'

    default:
      return ''
  }
}

function matchSearch(page, search) {
  if (!search) {
    return true
  }

  const regex = new RegExp(search+'(?!([^<]+)?>)', 'gi')

  return -1 !== page.title.search(regex)
    || (!isEmpty(page.content) && -1 !== page.content.search(regex))
    || (!isEmpty(page.internalNote) && -1 !== page.internalNote.search(regex))
}

function highlightSearch(text, search) {
  if (!search) {
    return text
  }

  return text.replace(new RegExp(search+'(?!([^<]+)?>)', 'gi'), '<mark class="text-highlight p-0">$&</mark>')
}

export {
  getNumbering,
  matchSearch,
  highlightSearch
}
