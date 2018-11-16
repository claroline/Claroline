import {bootstrap} from '#/main/app/dom/bootstrap'

import {App} from '#/plugin/tag/administration/tags'

// generate application
const TagsApp = new App()

// mount the react application
bootstrap('.tags-container', TagsApp.component, TagsApp.store, TagsApp.initialData)
