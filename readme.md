Power Tools for Action Scheduler
--------------------------------

To install, simply clone (or copy) the repository to your plugin directory (typically, this is `wp-content/plugins/`). Then, run `npm i` followed by `npm run build`. After activation, you should find that the existing **Tools ▸ Scheduled Actions** screen now has an option to open powertools, which will lead to:

- A settings panel, from which various performance characteristics can be managed.
- A diagnostics panel, which may be helpful when assessing the health of your action queue.

This plugin is partly a proof-of-concept, so is not feature complete and is not production ready. Expect brokenness until further notice 🙃

### Screenshots

![Tuning/performance settings](https://share.codingkills.me/demo/wordpress/aspowertools-010-tuning.png)

👆🏽 Makes it possible to modify various performance characteristics without resorting to code snippets.

![Diagnostics](https://share.codingkills.me/demo/wordpress/aspowertools-010-diagnostics.png)

👆🏽 Looks for problems and provides information about the current Action Scheduler setup.
