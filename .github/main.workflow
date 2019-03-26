workflow "Build and publish assets" {
  on = "push"
  resolves = ["Commit and push assets"]
}

action "Build assets" {
  uses = "elstudio/actions-js-build/build@master"
  args = "default"
}

action "Commit and push assets" {
  uses = "elstudio/actions-js-build/commit@master"
  needs = ["Build assets"]
  secrets = ["GITHUB_TOKEN"]
}
