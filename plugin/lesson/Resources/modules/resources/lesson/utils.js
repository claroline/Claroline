import {trans} from '#/main/app/intl/translation'

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
 * Flattens a tree of steps into a one-level array.
 *
 * @param {Array}  chapters
 */
function flattenChapters(chapters) {
  function flatten(step, parent = null) {
    const children = step.children
    const flatStep = Object.assign({}, step)

    delete flatStep.children
    if (parent) {
      flatStep.parent = {
        id: parent.id,
        title: parent.title
      }
    }

    let flattened = [flatStep]

    if (children) {
      children.map((child) => {
        flattened = flattened.concat(flatten(child, flatStep))
      })
    }

    return flattened
  }

  return chapters.reduce((acc, step) => acc.concat(flatten(step)), [])
}

export {
  flattenChapters,
  buildParentChapterChoices
}
