<template>
  <el-dialog :visible="visible" :width="width" custom-class="dialog-form" @close="close">
    <el-scrollbar>
      <slot />
    </el-scrollbar>
    <div slot="footer" class="dialog-footer">
      <el-button @click="cancel">取 消</el-button>
      <el-button type="primary" :loading="confirmBtnLoading" @click="confirm">确 定</el-button>
    </div>
  </el-dialog>
</template>

<script>
export default {
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    confirmBtnLoading: {
      type: Boolean,
      default: false
    },
    width: {
      type: String,
      default: '50%'
    },
    height: {
      type: String,
      default: '80%'
    }
  },
  mounted() {
    const dialogFormEl = this.$el.getElementsByClassName('dialog-form')[0]
    dialogFormEl.style.height = this.height
    dialogFormEl.style.top = `${(100 - parseInt(this.height)) / 2}%`
  },
  methods: {
    confirm() {
      this.$emit('confirm')
    },
    cancel() {
      this.$emit('update:visible', false)
      this.$emit('cancel')
    },
    close() {
      this.$emit('update:visible', false)
      this.$emit('close')
    }
  }
}
</script>

<style lang="scss" scoped>
::v-deep .dialog-form {
  margin-top: 0 !important;
  .el-dialog__body {
    padding: 30px 20px 10px;
    height: calc(100% - 90px);
  }
  .el-dialog__footer {
    padding: 10px;
    text-align: right;
    position: absolute;
    right: 0;
    bottom: 0;
    width: 100%;
  }
  .el-scrollbar {
    height: 100%;
    .el-scrollbar__wrap {
      overflow-x: hidden;
      .el-scrollbar__view {
        .el-form {
          margin: 0 20px;
        }
      }
    }
  }
}
</style>
