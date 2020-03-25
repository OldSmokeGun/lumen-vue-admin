<template>
  <section class="app-main">
    <el-scrollbar wrap-class="app-main-scrollbar">
      <transition name="fade-transform" mode="out-in">
        <router-view :key="key" />
      </transition>
    </el-scrollbar>
  </section>
</template>

<script>
export default {
  name: 'AppMain',
  computed: {
    key() {
      return this.$route.path
    }
  }
}
</script>

<style scoped>
.app-main {
  /*50 = navbar  */
  min-height: calc(100vh - 50px);
  width: 100%;
  position: relative;
  overflow: hidden;
}
.fixed-header + .app-main {
  padding-top: 50px;
}
/deep/ .app-main-scrollbar {
  height: calc(100vh - 50px);
}
</style>

<style lang="scss">
// fix css style bug in open el-dialog
.el-popup-parent--hidden {
  .fixed-header {
    padding-right: 15px;
  }
}
</style>
