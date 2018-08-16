import cloneDeep from 'lodash/cloneDeep'
import {trans} from '#/main/core/translation'

import {LINK_BUTTON} from '#/main/app/buttons'

/**
 * Transforme l'arbre du cours provenant de l'API pour que le composant Summary puisse l'utiliser
 *
 * @param tree
 */
export const normalizeTree = (tree, lessonId, canEdit) => {

  const copy = cloneDeep(tree)

  let elems = normalizeTreeNode(copy.children, lessonId, canEdit)
  elems.push({
    label: trans('chapter_creation', {}, 'icap_lesson'),
    target: '/new',
    icon: 'fa fa-fw fa-plus',
    type: LINK_BUTTON
  })

  return {
    id: tree.id,
    slug: tree.slug,
    children: elems
  }
}

const normalizeTreeNode = (node, lessonId, canEdit) => {

  return node.map((elem) => {

    const element = {
      type: LINK_BUTTON,
      target: `/${elem['slug']}`,
      label: elem['title'],
      additional: [
        {
          type: LINK_BUTTON,
          target: `/${elem['slug']}/edit`,
          label: trans('edit_chapter_button', {}, 'icap_lesson'),
          icon: 'fa fa-pencil',
          displayed: canEdit
        },
        {
          type: LINK_BUTTON,
          target: `/${elem['slug']}/copy`,
          label: trans('copy'),
          icon: 'fa fa-copy',
          displayed: canEdit
        }
      ]
    }

    if (elem.children.length > 0) {
      element.children = normalizeTreeNode(elem.children, lessonId, canEdit)
    }

    return element
  })
}

export const buildParentChapterChoices = (tree, chapter) => {
  let chapterSlug = chapter ? chapter.slug : null

  let flattenedChapters = {}
  flattenedChapters[tree['slug']] = trans('Root', {}, 'icap_lesson')

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