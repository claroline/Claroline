import {bootstrap} from '#/main/app/bootstrap'

import {App} from '#/plugin/blog/resources/blog'

// generate application
const BlogApp = new App()

// mount the react application
bootstrap('.blog-container', BlogApp.component, BlogApp.store, BlogApp.initialData)
