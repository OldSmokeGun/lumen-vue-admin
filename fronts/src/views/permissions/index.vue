<template>
  <div class="app-container">
    <el-form :inline="true" :model="search" class="demo-form-inline">
      <el-form-item>
        <el-input
          v-model="search.fields.identification"
          placeholder="唯一标识"
          @keyup.enter.native="handleSearchList"
        />
      </el-form-item>
      <el-form-item>
        <el-input
          v-model="search.fields.title"
          placeholder="标题"
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
          v-permission="'/api/permissions/create'"
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
        fit
        style="width: 100%"
        row-key="identification"
        element-loading-text="拼命加载中"
        :data="table.tableList"
        :border="false"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column
          label="标题"
          prop="title"
          :show-overflow-tooltip="true"
        />
        <el-table-column
          label="唯一标识"
          prop="identification"
          :show-overflow-tooltip="true"
        />
        <el-table-column
          label="重定向"
          prop="redirect"
          :show-overflow-tooltip="true"
        />
        <el-table-column
          label="描述"
          prop="description"
          :show-overflow-tooltip="true"
        />
        <el-table-column label="图标" prop="icon" align="center" width="80">
          <template slot-scope="scope">
            <svg-icon :icon-class="scope.row.icon" />
          </template>
        </el-table-column>
        <el-table-column label="排序" prop="sort" align="center" width="80" />
        <el-table-column label="路由类型" prop="status" align="center" width="100">
          <template slot-scope="scope">
            <el-tag :type="scope.row.type | typeFilter">{{
              table.typeMap[scope.row.type]
            }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" prop="status" align="center" width="80">
          <template slot-scope="scope">
            <el-tag :type="scope.row.status | statusFilter">{{
              scope.row.status ? "启用" : "禁用"
            }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="显隐" prop="display" align="center" width="80">
          <template slot-scope="scope">
            <el-tag :type="scope.row.display ? 'success' : 'info'">{{
              scope.row.display ? "显示" : "隐藏"
            }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column
          v-if="
            $permission([
              '/api/permissions/edit',
              '/api/permissions/update',
              '/api/permissions/delete',
            ])
          "
          label="操作"
          align="center"
        >
          <template slot-scope="scope">
            <el-button
              v-permission="'/api/permissions/update'"
              class="action-bar-text-button-forbidden"
              type="text"
              icon="el-icon-unlock"
              size="mini"
              :loading="scope.row.editStatusBtnLoading"
              @click="handleEditStatus(scope)"
            >{{ scope.row.status ? "禁用" : "启用" }}</el-button>
            <el-button
              v-permission="'/api/permissions/update'"
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
                v-permission="'/api/permissions/delete'"
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

    <dialog-form
      :visible.sync="form.formIsVisible"
      width="40%"
      :confirm-btn-loading="form.formBtnLoading"
      @confirm="handleIssueForm"
    >
      <template>
        <el-form
          ref="form"
          :model="form.fields"
          :rules="form.rules"
          label-width="100px"
          :validate-on-rule-change="false"
        >
          <el-form-item label="唯一标识" prop="identification">
            <el-input
              v-model="form.fields.identification"
              placeholder="如果以 http: 或 https: 开头，则「组件」和「重定向」字段设置无效"
            />
          </el-form-item>
          <el-form-item label="标题" prop="title">
            <el-input
              v-model="form.fields.title"
              placeholder="请输入权限标题"
            />
          </el-form-item>
          <el-form-item label="描述" prop="description">
            <el-input
              v-model="form.fields.description"
              placeholder="请填写权限描述"
            />
          </el-form-item>
          <el-form-item label="重定向" prop="redirect">
            <el-input
              v-model="form.fields.redirect"
              placeholder="请输入重定向路径"
              :disabled="form.fieldsDisabled.redirect"
            />
          </el-form-item>
          <el-form-item label="图标" prop="icon">
            <el-select
              v-model="form.fields.icon"
              placeholder="请选择权限图标"
              style="width: 100%"
              :disabled="form.fieldsDisabled.icon"
            >
              <el-option
                v-for="icon in icons"
                :key="icon"
                :label="icon"
                :value="icon"
              >
                <span style="float: left">
                  <svg-icon :icon-class="icon" />
                </span>
                <span style="float: right; color: #8492a6; font-size: 13px">{{
                  icon
                }}</span>
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="排序">
            <el-input-number v-model="form.fields.sort" />
          </el-form-item>
          <el-form-item label="状态">
            <el-switch
              v-model="form.fields.status"
              :active-value="1"
              :inactive-value="0"
              validate-event
            />
          </el-form-item>
          <el-form-item label="是否显示">
            <el-switch
              v-model="form.fields.display"
              :active-value="1"
              :inactive-value="0"
              validate-event
              :disabled="form.fieldsDisabled.display"
            />
          </el-form-item>
          <el-form-item label="路由类型">
            <el-radio-group v-model="form.fields.type">
              <el-radio :label="0">页面类型</el-radio>
              <el-radio :label="1">按钮类型</el-radio>
              <el-radio :label="2">其它类型</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="上级权限">
            <el-tree
              ref="routeTree"
              :data="form.treeData"
              :highlight-current="true"
              :accordion="true"
              :props="{ label: 'tree_title' }"
              node-key="id"
              style="margin-top: 7px"
              :default-expanded-keys="[form.defaultExpandedNodeId]"
              @node-click="handleTreeClick"
            />
          </el-form-item>
        </el-form>
      </template>
    </dialog-form>
  </div>
</template>

<script>
import {
  getPermissions,
  createPermission,
  updatePermission,
  deletePermission,
  getCreatePermissionFormData,
  getUpdatePermissionFormData
} from '@/api/permissions'
import svgIconNames from '@/utils/get-svg'
import { isExternal } from '@/utils/validate'
import DialogForm from '@/views/components/DialogForm'

export default {
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: 'success',
        0: 'danger'
      }
      return statusMap[status]
    },
    typeFilter(type) {
      const typeMap = {
        0: '',
        1: 'success',
        2: 'info'
      }
      return typeMap[type]
    }
  },
  components: { DialogForm },
  data() {
    return {
      icons: [],
      table: {
        tableKey: 0,
        tableList: [],
        tableListLoading: true,
        typeMap: {
          0: '页面类型',
          1: '按钮类型',
          2: '其它类型'
        }
      },
      search: {
        searchBtnLoding: false,
        fields: {
          identification: '',
          title: '',
          type: '',
          status: ''
        }
      },
      form: {
        fields: {
          identification: '',
          title: '',
          icon: '',
          redirect: '',
          description: '',
          type: 0,
          parent_id: 0,
          sort: 0,
          status: 1,
          display: 1
        },
        rules: {
          identification: [{ required: true, trigger: 'blur', message: '请填写权限唯一标识' }],
          title: [{ required: true, trigger: 'blur', message: '请填写权限标题' }]
        },
        formBtnLoading: false,
        formIsVisible: false,
        fieldsDisabled: {
          redirect: false,
          icon: false,
          display: false
        },
        treeData: [],
        defaultExpandedNodeId: 0
      }
    }
  },
  watch: {
    'form.fields': {
      handler() {
        if (!this.form.fields.type) {
          if (isExternal(this.form.fields.identification)) {
            this.form.fields.redirect = ''
            this.form.fieldsDisabled.redirect = true
          } else {
            this.form.fieldsDisabled.redirect = false
          }
          this.form.fieldsDisabled.icon = false
          this.form.fieldsDisabled.display = false
        } else {
          this.form.fields.redirect = ''
          this.form.fields.icon = ''
          this.form.fields.display = 0

          this.form.fieldsDisabled.redirect = true
          this.form.fieldsDisabled.icon = true
          this.form.fieldsDisabled.display = true
        }
      },
      deep: true
    }
  },
  created() {
    this.handleList()
    this.icons = svgIconNames
  },
  methods: {
    handleList() {
      this.table.tableListLoading = true
      getPermissions(this.search.fields)
        .then((response) => {
          this.table.tableListLoading = false
          this.search.searchBtnLoding = false
          this.table.tableList = response.data.data
        })
        .catch((e) => {
          this.table.tableListLoading = false
          this.search.searchBtnLoding = false
        })
    },
    handleAdd() {
      getCreatePermissionFormData().then((response) => {
        this.form.treeData = response.data.data
        this.$nextTick(() => {
          this.$refs.routeTree.setCurrentKey(0)
        })
      })

      delete this.form.fields.id
      Object.assign(this.form.fields, {
        identification: '',
        title: '',
        icon: '',
        component: '',
        redirect: '',
        description: '',
        type: 0,
        parent_id: 0,
        sort: 0,
        status: 1,
        display: 1
      })

      this.form.formIsVisible = true
      this.$nextTick(() => {
        this.$refs.form.clearValidate()
      })
    },
    handleUpdate(scope) {
      getUpdatePermissionFormData(scope.row.id).then((response) => {
        this.form.treeData = response.data.data
        this.$nextTick(() => {
          this.$refs.routeTree.setCurrentKey(Number(scope.row.parent_id))
          this.form.defaultExpandedNodeId = scope.row.parent_id
            ? Number(scope.row.parent_id)
            : 0
        })
      })

      Object.assign(this.form.fields, {
        id: scope.row.id,
        identification: scope.row.identification,
        title: scope.row.title,
        icon: scope.row.icon,
        component: scope.row.component,
        redirect: scope.row.redirect,
        description: scope.row.description,
        type: scope.row.type,
        parent_id: scope.row.parent_id,
        sort: scope.row.sort,
        status: scope.row.status,
        display: scope.row.display
      })

      this.form.formIsVisible = true
      this.$nextTick(() => {
        this.$refs.form.clearValidate()
      })
    },
    handleDelete(scope) {
      this.$set(scope.row, 'deleteBtnLoading', true)

      deletePermission(scope.row.id)
        .then((response) => {
          this.$delete(scope.row, 'deleteBtnLoading')
          if (response.data.code === 'OK') {
            this.$message.success('删除成功')
            this.handleList()
          }
        })
        .catch((e) => {
          this.$delete(scope.row, 'deleteBtnLoading')
        })
    },
    handleSearchList() {
      this.search.searchBtnLoding = true
      this.handleList()
    },
    handleIssueForm() {
      this.$refs.form.validate((valid) => {
        if (!valid) return false

        this.form.formBtnLoading = true

        if (this.form.fields.id) {
          updatePermission(this.form.fields)
            .then((response) => {
              this.handleFormSuccess(response.data.code)
            })
            .catch((e) => {
              this.form.formBtnLoading = false
            })
        } else {
          createPermission(this.form.fields)
            .then((response) => {
              this.handleFormSuccess(response.data.code)
            })
            .catch((e) => {
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

      updatePermission(params)
        .then((response) => {
          this.$delete(scope.row, 'editStatusBtnLoading')
          if (response.data.code === 'OK') {
            scope.row.status = Number(!scope.row.status)
            this.$message.success(`成功${willDoAction}`)
          }
        })
        .catch((e) => {
          this.$delete(scope.row, 'editStatusBtnLoading')
        })
    },
    handleTreeClick(data) {
      this.form.fields.parent_id = data.id
    }
  }
}
</script>

<style lang="scss" scope>
.action-bar-text-button-delete {
  margin-left: 10px;
}
</style>
