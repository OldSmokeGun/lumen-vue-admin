<template>
  <div class="app-container">
    <el-form :inline="true" :model="search" class="demo-form-inline">
      <el-form-item>
        <el-input
          v-model="search.fields.username"
          placeholder="用户名"
          @keyup.enter.native="handleSearchList"
        />
      </el-form-item>
      <el-form-item>
        <el-input
          v-model="search.fields.nickname"
          placeholder="昵称"
          @keyup.enter.native="handleSearchList"
        />
      </el-form-item>
      <el-form-item>
        <el-input
          v-model="search.fields.email"
          placeholder="邮箱"
          @keyup.enter.native="handleSearchList"
        />
      </el-form-item>
      <el-form-item>
        <el-select v-model="search.fields.status" placeholder="状态">
          <el-option label="全部状态" value />
          <el-option label="启用" value="1" />
          <el-option label="禁用" value="0" />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button
          icon="el-icon-search"
          :loading="search.searchBtnLoding"
          @click="handleSearchList"
        >查询</el-button>
        <el-button
          v-permission="'/api/admins/create'"
          type="primary"
          icon="el-icon-circle-plus-outline"
          @click="handleAdd"
        >添加</el-button>
      </el-form-item>
    </el-form>

    <el-scrollbar>
      <el-table
        :key="table.tableKey"
        v-loading="table.tableListLoading"
        :data="table.tableList"
        :border="false"
        element-loading-text="拼命加载中"
        :fit="true"
        style="width: 100%;"
      >
        <el-table-column type="expand">
          <template slot-scope="scope">
            <el-form label-position="left" inline class="admin-table-expand">
              <el-form-item label="创建时间">
                <span>{{ scope.row.create_time }}</span>
              </el-form-item>
              <el-form-item label="修改时间">
                <span>{{ scope.row.update_time }}</span>
              </el-form-item>
              <el-form-item label="最后登陆时间">
                <span>{{ scope.row.last_login_time }}</span>
              </el-form-item>
              <el-form-item label="最后登陆 IP">
                <span>{{ scope.row.last_login_ip }}</span>
              </el-form-item>
            </el-form>
          </template>
        </el-table-column>
        <el-table-column label="头像" prop="avatar" width="100">
          <template slot-scope="scope">
            <el-image
              style="width: 50px; height: 50px; display: inline-table;"
              :src="scope.row.avatar"
              :preview-src-list="[scope.row.avatar]"
            >
              <div slot="error" class="image-slot">
                <i class="el-icon-picture-outline" />
              </div>
            </el-image>
          </template>
        </el-table-column>
        <el-table-column label="用户名" prop="username" />
        <el-table-column label="昵称" prop="nickname" />
        <el-table-column label="邮箱" prop="email" />
        <el-table-column label="角色" prop="roles">
          <template slot-scope="scope">
            <el-tag
              v-for="item in scope.row.roles"
              :key="item.i"
              style="margin-right: 5px;"
            >{{ item.name }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" prop="status" align="center">
          <template slot-scope="scope">
            <el-tag :type="scope.row.status | statusFilter">{{ scope.row.status ? '启用' : '禁用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column
          v-if="$permission(['/api/admins/edit', '/api/admins/update', '/api/admins/delete'])"
          label="操作"
          align="center"
        >
          <template slot-scope="scope">
            <el-button
              v-permission="'/api/admins/edit'"
              class="action-bar-text-button-forbidden"
              type="text"
              icon="el-icon-unlock"
              size="mini"
              :loading="scope.row.editStatusBtnLoading"
              @click="handleEditStatus(scope)"
            >{{ scope.row.status ? '禁用' : '启用' }}</el-button>
            <el-button
              v-permission="'/api/admins/update'"
              class="action-bar-text-button-edit"
              type="text"
              icon="el-icon-edit"
              size="mini"
              @click="handleUpdate(scope)"
            >编辑</el-button>
            <el-popconfirm
              title="确定要删除吗？"
              placement="top"
              cancel-button-type="primary"
              confirm-button-type="text"
              @onConfirm="handleDelete(scope)"
            >
              <el-button
                slot="reference"
                v-permission="'/api/admins/delete'"
                class="action-bar-text-button-delete"
                type="text"
                :loading="scope.row.deleteBtnLoading"
                icon="el-icon-delete"
                size="mini"
              >删除</el-button>
            </el-popconfirm>
          </template>
        </el-table-column>
      </el-table>
    </el-scrollbar>

    <pagination
      :total="pagination.total"
      :page.sync="pagination.current_page"
      :limit.sync="pagination.limit"
      @pagination="handlePagenation"
    />

    <dialog-form
      :visible.sync="form.formIsVisible"
      width="30%"
      :confirm-btn-loading="form.formBtnLoading"
      @confirm="handleIssueForm"
    >
      <template>
        <el-form ref="form" :model="form.fields" :rules="form.rules" label-width="100px">
          <el-form-item label="用户名" prop="username">
            <el-input v-model="form.fields.username" placeholder="请输入用户名" />
          </el-form-item>
          <el-form-item label="昵称" prop="nickname">
            <el-input v-model="form.fields.nickname" placeholder="请输入昵称" />
          </el-form-item>
          <el-form-item label="密码" prop="password">
            <el-input v-model="form.fields.password" placeholder="请输入密码" show-password />
          </el-form-item>
          <el-form-item label="邮箱" prop="email">
            <el-input v-model="form.fields.email" placeholder="请输入邮箱" />
          </el-form-item>
          <el-form-item v-model="form.fields.avatar" label="头像">
            <el-upload
              ref="upload"
              name="avatar"
              action="/api/admins/upload"
              class="avatar-uploader"
              accept="image/*"
              :headers="{Authorization: getToken()}"
              :auto-upload="true"
              :show-file-list="false"
              :on-success="handleAvatarSuccess"
              :before-upload="handleBeforeAvatarUpload"
              :on-progress="handleOnProgress"
            >
              <img v-if="form.fields.avatar" :src="form.fields.avatar" class="avatar">
              <i v-else class="el-icon-plus avatar-uploader-icon" />
            </el-upload>
            <div v-if="avatarUpload.showAvatarUploadProgress" class="avatar-upload-progress">
              <el-progress
                v-if="avatarUpload.uploadProgressStatus == ''"
                :percentage="avatarUpload.uploadPercent"
              />
              <el-progress
                v-if="avatarUpload.uploadProgressStatus == 'success'"
                :percentage="avatarUpload.uploadPercent"
                status="success"
              />
            </div>
          </el-form-item>
          <el-form-item label="角色" prop="roles">
            <el-select
              v-model="form.fields.roles"
              multiple
              placeholder="请选择"
              style="width: 100%"
              :multiple-limit="3"
            >
              <el-option
                v-for="role in form.roles"
                :key="role.id"
                :label="role.name"
                :value="role.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="状态">
            <el-switch
              v-model="form.fields.status"
              :active-value="1"
              :inactive-value="0"
              validate-event
            />
          </el-form-item>
        </el-form>
      </template>
    </dialog-form>
  </div>
</template>

<script>
import { getList, createAdmin, updateAdmin, editAdmin, deleteAdmin, getRoles } from '@/api/admin'
import Pagination from '@/components/Pagination'
import { getToken } from '@/utils/cookie'
import DialogForm from '@/views/components/DialogForm'

export default {
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: 'success',
        0: 'danger'
      }
      return statusMap[status]
    }
  },
  components: { Pagination, DialogForm },
  data() {
    return {
      table: {
        tableKey: 0,
        tableList: [],
        tableListLoading: true
      },
      search: {
        searchBtnLoding: false,
        fields: {
          username: '',
          nickname: '',
          email: '',
          status: '',
          page: 1,
          limit: 10
        }
      },
      form: {
        formBtnLoading: false,
        formIsVisible: false,
        fields: {
          username: '',
          nickname: '',
          password: '',
          avatar: '',
          email: '',
          status: 1,
          roles: []
        },
        rules: {
          username: [
            { required: true, trigger: 'blur', message: '请填写用户名' }
          ],
          nickname: [
            { required: true, trigger: 'blur', message: '请填写昵称' }
          ],
          password: [],
          email: [
            { required: true, trigger: 'blur', message: '请填写邮箱' }
          ]
        },
        roles: []
      },
      avatarUpload: {
        uploadProgressStatus: '',
        showAvatarUploadProgress: false,
        uploadPercent: 0
      },
      pagination: {
        page: 1,
        limit: 10,
        total: 0
      }
    }
  },
  created() {
    this.handleList()
  },
  methods: {
    handleList() {
      this.table.tableListLoading = true

      getList(this.search.fields).then(response => {
        this.table.tableListLoading = false
        this.search.searchBtnLoding = false
        this.table.tableList = response.data.data.data
        this.pagination.page = Number(response.data.data.current_page)
        this.pagination.total = Number(response.data.data.total)
        this.pagination.limit = Number(response.data.data.per_page)
      }).catch(e => {
        this.table.tableListLoading = false
        this.search.searchBtnLoding = false
      })
    },
    handleAdd() {
      this.form.rules.password.push({ required: true, trigger: 'blur', message: '请填写密码' })
      Object.assign(this.avatarUpload, {
        uploadProgressStatus: '',
        showAvatarUploadProgress: false,
        uploadPercent: 0
      })

      getRoles().then(response => {
        this.form.roles = response.data.data
      })

      delete this.form.fields.id
      Object.assign(this.form.fields, {
        username: '',
        nickname: '',
        password: '',
        avatar: '',
        email: '',
        status: 1,
        roles: []
      })

      this.form.formIsVisible = true
      this.$nextTick(() => {
        this.$refs.form.clearValidate()
      })
    },
    handleUpdate(scope) {
      this.form.rules.password = []
      Object.assign(this.avatarUpload, {
        uploadProgressStatus: '',
        showAvatarUploadProgress: false,
        uploadPercent: 0
      })

      getRoles().then(response => {
        this.form.roles = response.data.data
      })

      const roles = []
      for (const role of scope.row.roles) {
        roles.push(role.id)
      }

      Object.assign(this.form.fields, {
        id: scope.row.id,
        username: scope.row.username,
        nickname: scope.row.nickname,
        password: '',
        avatar: scope.row.avatar,
        email: scope.row.email,
        status: scope.row.status,
        roles: roles
      })

      this.form.formIsVisible = true
      this.$nextTick(() => {
        this.$refs.form.clearValidate()
      })
    },
    handleDelete(scope) {
      this.$set(scope.row, 'deleteBtnLoading', true)

      deleteAdmin(scope.row.id).then(response => {
        this.$delete(scope.row, 'deleteBtnLoading')
        if (response.data.code === 'OK') {
          this.$message.success('删除成功')
          this.handleList()
        }
      }).catch(e => {
        this.$delete(scope.row, 'deleteBtnLoading')
      })
    },
    handlePagenation(pagenation) {
      this.search.fields.page = pagenation.page
      this.search.fields.limit = pagenation.limit
      this.handleList()
    },
    handleSearchList() {
      this.search.searchBtnLoding = true
      this.handleList()
    },
    handleIssueForm() {
      this.$refs.form.validate(valid => {
        if (!valid) return false

        if (this.form.fields.avatar.length === 0) {
          this.$message.error('请上传头像')
          return false
        }

        this.form.formBtnLoading = true

        if (this.form.fields.id) {
          updateAdmin(this.form.fields).then(response => {
            this.handleFormSuccess(response.data.code)
          }).catch(e => {
            this.form.formBtnLoading = false
          })
        } else {
          createAdmin(this.form.fields).then(response => {
            this.handleFormSuccess(response.data.code)
          }).catch(e => {
            this.form.formBtnLoading = false
          })
        }
        this.form.formIsVisible = true
      })
    },
    handleFormSuccess(code) {
      this.form.formBtnLoading = false

      if (code === 'OK') {
        this.form.formIsVisible = false
        this.$message.success('数据保存成功')
        this.handleList()
      }
    },
    handleEditStatus(scope) {
      this.$set(scope.row, 'editStatusBtnLoading', true)

      const willDoAction = scope.row.status ? '禁用' : '启用'
      const params = {
        id: scope.row.id,
        status: Number(!scope.row.status)
      }

      editAdmin(params).then(response => {
        this.$delete(scope.row, 'editStatusBtnLoading')
        if (response.data.code === 'OK') {
          scope.row.status = Number(!scope.row.status)
          this.$message.success(`成功${willDoAction}`)
        }
      }).catch(e => {
        this.$delete(scope.row, 'editStatusBtnLoading')
      })
    },
    getToken() {
      return getToken()
    },
    handleBeforeAvatarUpload(file) {
      if (this.avatarUpload.showAvatarUploadProgress) {
        Object.assign(this.avatarUpload, {
          showAvatarUploadProgress: !this.avatarUpload.showAvatarUploadProgress,
          uploadProgressStatus: '',
          uploadPercent: 0
        })
      }

      if (file.type !== 'image/jpeg' && file.type !== 'image/png') {
        this.$message.error('上传头像图片只能是 JPG 或 PNG 格式！')
        return false
      }

      if (file.size / 1024 / 1024 > 2) {
        this.$message.error('上传头像图片大小不能超过 2MB！')
        return false
      }

      this.avatarUpload.showAvatarUploadProgress = !this.avatarUpload.showAvatarUploadProgress

      return true
    },
    handleOnProgress(event, file, fileList) {
      this.avatarUpload.uploadPercent = event.percent
    },
    handleAvatarSuccess(res, file) {
      this.form.fields.avatar = res.data.avatar_path
      this.avatarUpload.uploadProgressStatus = 'success'
    }

  }
}
</script>

<style lang="scss" scope>
.image-slot {
  font-size: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
  background: #f5f7fa;
  color: #909399;
}
.avatar-uploader .el-upload {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}
.avatar-uploader .el-upload:hover {
  border-color: #409eff;
}
.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 100px;
  height: 100px;
  line-height: 100px;
  text-align: center;
}
.avatar {
  width: 100px;
  height: 100px;
  display: block;
}

.avatar-upload-progress {
  .el-progress {
    width: 152px;
  }
}

.admin-table-expand {
  font-size: 0;
}
.admin-table-expand label {
  width: 150px;
  color: #99a9bf;
}
.admin-table-expand .el-form-item {
  margin-right: 0;
  margin-bottom: 0;
  width: 100%;
}

.action-bar-text-button-delete {
  margin-left: 10px;
}
</style>
