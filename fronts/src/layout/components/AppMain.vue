<template>
  <section class="app-main">
    <el-scrollbar wrap-class="app-main-scrollbar">
      <transition name="fade-transform" mode="out-in">
        <router-view :key="key" />
      </transition>
    </el-scrollbar>
    <dialog-form
      :visible.sync="resetPasswordForm.formIsVisible"
      width="30%"
      height="35%"
      :confirm-btn-loading="resetPasswordForm.formBtnLoading"
      @confirm="handleIssueForm"
    >
      <template>
        <el-form ref="resetPasswordForm" :model="resetPasswordForm.fields" :rules="resetPasswordForm.rules" label-width="100px">
          <el-form-item label="原密码" prop="old_password">
            <el-input v-model="resetPasswordForm.fields.old_password" type="password" placeholder="请输入原密码" />
          </el-form-item>
          <el-form-item label="新密码" prop="new_password">
            <el-input v-model="resetPasswordForm.fields.new_password" type="password" placeholder="请输入新密码" />
          </el-form-item>
          <el-form-item label="确认密码" prop="check_password">
            <el-input v-model="resetPasswordForm.fields.check_password" type="password" placeholder="请确认新密码" />
          </el-form-item>
        </el-form>
      </template>
    </dialog-form>
  </section>
</template>

<script>
import DialogForm from '@/views/components/DialogForm'
import { logout, updatePassword } from '@/api/admins'
import { getToken } from '@/utils/cookie'

export default {
  name: 'AppMain',
  components: {
    DialogForm
  },
  data() {
    return {
      resetPasswordForm: {
        formBtnLoading: false,
        formIsVisible: false,
        fields: {
          old_password: '',
          new_password: '',
          check_password: ''
        },
        rules: {
          old_password: [
            { required: true, trigger: 'blur', message: '请填写原密码' }
          ],
          new_password: [
            { required: true, trigger: 'blur', message: '请填写新密码' }
          ],
          check_password: [
            { required: true, validator: (rule, value, callback) => {
              if (!value) {
                callback(new Error('请填写确认密码'))
              }

              if (value !== this.resetPasswordForm.fields.new_password) {
                callback(new Error('两次密码不一致'))
              } else {
                callback()
              }
            }, trigger: 'blur' }
          ]
        }
      }
    }
  },
  computed: {
    key() {
      return this.$route.path
    }
  },
  methods: {
    handleResetPassword() {
      this.resetPasswordForm.formIsVisible = true
      this.resetPasswordForm.fields.old_password = ''
      this.resetPasswordForm.fields.new_password = ''
      this.resetPasswordForm.fields.check_password = ''
      this.$nextTick(() => {
        this.$refs.resetPasswordForm.clearValidate()
      })
    },
    handleIssueForm() {
      this.$refs.resetPasswordForm.validate(valid => {
        if (!valid) return false

        this.resetPasswordForm.formBtnLoading = true

        updatePassword({
          token: getToken(),
          old_password: this.resetPasswordForm.fields.old_password,
          new_password: this.resetPasswordForm.fields.new_password
        }).then(response => {
          this.resetPasswordForm.formBtnLoading = false

          if (response.data.code === 'OK') {
            this.resetPasswordForm.formIsVisible = false
            logout(getToken())
          }
        }).catch(e => {
          this.resetPasswordForm.formBtnLoading = false
        })
      })
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
