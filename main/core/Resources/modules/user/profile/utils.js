import {t} from '#/main/core/translation'

function getMainFacet(facets) {
  return facets.find(facet => facet.meta.main)
}

function getDefaultFacet() {
  return {
    id: 'main',
    title: t('general'),
    position: 0,
    meta: {
      main: true
    },
    sections: [

    ]
  }
}

function getDetailsDefaultSection() {
  return {
    id: 'default-props',
    title: t('general'),
    primary: true,
    fields: [
      {
        name: 'email',
        type: 'email',
        label: t('email')
      }, {
        name: 'meta.description',
        type: 'html',
        label: t('description'),
        options: {
          minRows: 5
        }
      }
    ]
  }
}

function getFormDefaultSection(userData, isNew = false) {
  return {
    id: 'default-props',
    title: t('general'),
    primary: true,
    fields: [
      {
        name: 'lastName',
        type: 'string',
        label: t('last_name'),
        required: true
      }, {
        name: 'firstName',
        type: 'string',
        label: t('first_name'),
        required: true
      }, {
        name: 'email',
        type: 'email',
        label: t('email'),
        required: true
      }, {
        name: 'username',
        type: 'username',
        label: t('username'),
        required: true,
        disabled: !isNew && (!userData.meta || !userData.meta.administrate)
      }, {
        name: 'plainPassword',
        type: 'password',
        label: t('password'),
        displayed: isNew,
        required: true
      }, {
        name: 'meta.description',
        type: 'html',
        label: t('description'),
        options: {
          minRows: 5
        }
      }, {
        name: 'picture',
        type: 'image',
        label: t('picture')
      }
    ]
  }
}

export {
  getDetailsDefaultSection,
  getFormDefaultSection,
  getMainFacet,
  getDefaultFacet
}
