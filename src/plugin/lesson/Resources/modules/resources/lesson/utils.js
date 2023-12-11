import {trans} from '#/main/app/intl/translation'
import {constants} from '#/plugin/path/resources/path/constants'

const buildParentChapterChoices = (tree, chapter) => {
  let chapterSlug = chapter ? chapter.slug : null

  let flattenedChapters = {}
  flattenedChapters[tree['slug']] = trans('Root', {}, 'lesson')

  if (tree['children'] && Array.isArray(tree['children']) && tree['children'].length > 0) {
    flattenedChapters = Object.assign(flattenedChapters, buildFlattenedChapterChoices(tree['children'], chapterSlug))
  }

  return flattenedChapters
}

const buildFlattenedChapterChoices = (items, chapterSlug) => {

  let flattenedChapters = {}

  items.forEach(item => {
    if (item['slug'] !== chapterSlug) {
      flattenedChapters[item['slug']] = item['title']
      if (item['children'] && Array.isArray(item['children']) && item['children'].length > 0) {
        flattenedChapters = Object.assign(flattenedChapters, buildFlattenedChapterChoices(item['children']))
      }
    }
  })

  return flattenedChapters
}

/**
 * Flattens a tree of chapters into a one-level array.
 *
 * @param {Array}  chapters
 */
function flattenChapters(chapters) {
  function flatten(chapter, parent = null) {
    const children = chapter.children
    const flatchapter = Object.assign({}, chapter)

    delete flatchapter.children
    if (parent) {
      flatchapter.parent = {
        id: parent.id,
        title: parent.title
      }
    }

    let flattened = [flatchapter]

    if (children) {
      children.map((child) => {
        flattened = flattened.concat(flatten(child, flatchapter))
      })
    }

    return flattened
  }

  return chapters.reduce((acc, chapter) => acc.concat(flatten(chapter)), [])
}

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
    case constants.NUMBERING_NUMERIC:
      return '' + buildPath(chapters, chapter)
        .map(i => i + 1)
        .join('.')
    case constants.NUMBERING_LITERAL:
      return buildPath(chapters, chapter)
        .map(i => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[i])
        .join('.')
    case constants.NUMBERING_CUSTOM:
      return chapter.customNumbering || ''
    case constants.NUMBERING_NONE:
    default:
      return ''
  }
}

export {
  flattenChapters,
  getNumbering,
  buildParentChapterChoices
}
