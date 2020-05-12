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

    <el-tabs ref="tabs" value="0" type="card" @tab-click="handleTabSwitch">
      <el-tab-pane label="路由类型权限" name="0">
        <el-scrollbar>
          <el-table
            :key="table.routeType.tableKey"
            v-loading="table.routeType.tableListLoading"
            fit
            style="width: 100%;"
            row-key="identification"
            element-loading-text="拼命加载中"
            :data="table.routeType.tableList"
            :border="false"
            :tree-props="{ children: 'children', hasChildren: 'hasChildren'} "
          >
            <el-table-column label="标题" prop="title" :show-overflow-tooltip="true" />
            <el-table-column label="唯一标识" prop="identification" :show-overflow-tooltip="true" />
            <el-table-column label="组件" prop="component" :show-overflow-tooltip="true" />
            <el-table-column label="重定向" prop="redirect" :show-overflow-tooltip="true" />
            <el-table-column label="描述" prop="description" :show-overflow-tooltip="true" />
            <el-table-column label="图标" prop="icon" align="center" width="80">
              <template slot-scope="scope">
                <svg-icon :icon-class="scope.row.icon" />
              </template>
            </el-table-column>
            <el-table-column label="排序" prop="sort" align="center" width="80" />
            <el-table-column label="状态" prop="status" align="center" width="80">
              <template slot-scope="scope">
                <el-tag :type="scope.row.status | statusFilter">{{ scope.row.status ? '启用' : '禁用' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="显隐" prop="display" align="center" width="80">
              <template slot-scope="scope">
                <el-tag
                  :type="scope.row.display ? 'success' : 'info'"
                >{{ scope.row.display ? '显示' : '隐藏' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column
              v-if="$permission(['/api/permissions/edit', '/api/permissions/update', '/api/permissions/delete'])"
              label="操作"
              align="center"
            >
              <template slot-scope="scope">
                <el-button
                  v-permission="'/api/permissions/edit'"
                  class="disabled-text-button-forbidden"
                  type="text"
                  icon="el-icon-unlock"
                  size="mini"
                  :loading="scope.row.editStatusBtnLoading"
                  @click="handleEditStatus(scope)"
                >{{ scope.row.status ? '禁用' : '启用' }}</el-button>
                <el-button
                  v-permission="'/api/permissions/update'"
                  class="disabled-text-button-edit"
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
                    class="disabled-text-button-delete"
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
      </el-tab-pane>
      <el-tab-pane label="其它类型权限" name="1">
        <el-scrollbar>
          <el-table
            :key="table.otherType.tableKey"
            v-loading="table.otherType.tableListLoading"
            fit
            style="width: 100%;"
            row-key="identification"
            element-loading-text="拼命加载中"
            :data="table.otherType.tableList"
            :border="false"
            :tree-props="{ children: 'children', hasChildren: 'hasChildren'} "
          >
            <el-table-column label="标题" prop="title" :show-overflow-tooltip="true" />
            <el-table-column label="唯一标识" prop="identification" :show-overflow-tooltip="true" />
            <el-table-column label="描述" prop="description" :show-overflow-tooltip="true" />
            <el-table-column label="排序" prop="sort" align="center" />
            <el-table-column label="状态" prop="status" align="center">
              <template slot-scope="scope">
                <el-tag :type="scope.row.status | statusFilter">{{ scope.row.status ? '启用' : '禁用' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column
              v-if="$permission(['/api/permissions/edit', '/api/permissions/update', '/api/permissions/delete'])"
              label="操作"
              align="center"
            >
              <template slot-scope="scope">
                <el-button
                  v-permission="'/api/permissions/edit'"
                  class="disabled-text-button-forbidden"
                  type="text"
                  icon="el-icon-unlock"
                  size="mini"
                  :loading="scope.row.editStatusBtnLoading"
                  @click="handleEditStatus(scope)"
                >{{ scope.row.status ? '禁用' : '启用' }}</el-button>
                <el-button
                  v-permission="'/api/permissions/update'"
                  class="disabled-text-button-edit"
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
                    class="disabled-text-button-delete"
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
      </el-tab-pane>
    </el-tabs>

    <dialog-form
      :visible.sync="form.routeType.formIsVisible"
      width="40%"
      :confirm-btn-loading="form.routeType.formBtnLoading"
      @confirm="handleIssueForm"
    >
      <template>
        <el-form
          ref="routeTypeForm"
          :model="form.fields"
          :rules="formRules"
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
            <el-input v-model="form.fields.title" placeholder="请输入权限标题" />
          </el-form-item>
          <el-form-item label="组件" prop="component">
            <el-input
              v-model="form.fields.component"
              placeholder="请输入权限组件名称"
              :disabled="form.routeType.fieldsDisabled.component"
            />
          </el-form-item>
          <el-form-item label="重定向" prop="redirect">
            <el-input
              v-model="form.fields.redirect"
              placeholder="请输入重定向路径"
              :disabled="form.routeType.fieldsDisabled.redirect"
            />
          </el-form-item>
          <el-form-item label="描述" prop="description">
            <el-input v-model="form.fields.description" placeholder="请填写权限描述" />
          </el-form-item>
          <el-form-item label="图标" prop="icon">
            <el-select v-model="form.fields.icon" placeholder="请选择权限图标" style="width: 100%;">
              <el-option v-for="icon in icons" :key="icon" :label="icon" :value="icon">
                <span style="float: left">
                  <svg-icon :icon-class="icon" />
                </span>
                <span style="float: right; color: #8492a6; font-size: 13px">{{ icon }}</span>
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
            />
          </el-form-item>
          <el-form-item label="上级权限">
            <el-tree
              ref="routeTypeTree"
              :data="form.routeType.treeData"
              :highlight-current="true"
              :accordion="true"
              :props="{ label: 'tree_title' }"
              node-key="id"
              style="margin-top: 7px;"
              :default-expanded-keys="[form.routeType.defaultExpandedNodeId]"
              @node-click="handleTreeClick"
            />
          </el-form-item>
        </el-form>
      </template>
    </dialog-form>

    <dialog-form
      :visible.sync="form.otherType.formIsVisible"
      width="40%"
      :confirm-btn-loading="form.otherType.formBtnLoading"
      @confirm="handleIssueForm"
    >
      <template>
        <el-form
          ref="otherTypeForm"
          :model="form.fields"
          :rules="formRules"
          label-width="100px"
          :validate-on-rule-change="false"
        >
          <el-form-item label="唯一标识" prop="identification">
            <el-input v-model="form.fields.identification" placeholder="请输入权限唯一标识" />
          </el-form-item>
          <el-form-item label="标题" prop="title">
            <el-input v-model="form.fields.title" placeholder="请输入权限标题" />
          </el-form-item>
          <el-form-item label="描述" prop="description">
            <el-input v-model="form.fields.description" placeholder="请填写权限描述" />
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
          <el-form-item label="上级权限">
            <el-tree
              ref="otherTypeTree"
              :data="form.otherType.treeData"
              :highlight-current="true"
              :accordion="true"
              :props="{ label: 'tree_title' }"
              node-key="id"
              style="margin-top: 7px;"
              :default-expanded-keys="[form.otherType.defaultExpandedNodeId]"
              @node-click="handleTreeClick"
            />
          </el-form-item>
        </el-form>
      </template>
    </dialog-form>
  </div>
</template>

<script>
import { getList, createPermission, updatePermission, editPermission, deletePermission, getPermissionTrees } from '@/api/permission'
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
    }
  },
  components: { DialogForm },
  data() {
    return {
      icons: [],
      table: {
        routeType: {
          tableKey: 0,
          tableList: [],
          tableListLoading: true
        },
        otherType: {
          tableKey: 0,
          tableList: [],
          tableListLoading: true
        }
      },
      search: {
        searchBtnLoding: false,
        fields: {
          identification: '',
          title: '',
          type: 0,
          status: ''
        }
      },
      form: {
        fields: {
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
        },
        routeType: {
          formBtnLoading: false,
          formIsVisible: false,
          fieldsDisabled: {
            title: false,
            icon: false
          },
          treeData: [],
          defaultExpandedNodeId: 0
        },
        otherType: {
          formBtnLoading: false,
          formIsVisible: false,
          treeData: [],
          defaultExpandedNodeId: 0
        }
      }
    }
  },
  computed: {
    formRules() {
      return {
        identification: [
          { required: true, trigger: 'blur', message: '请填写权限唯一标识' }
        ],
        title: [
          { required: true, trigger: 'blur', message: '请填写权限标题' }
        ]
      }
    }
  },
  watch: {
    form() {
      if (!this.form.fields.type) {
        if (isExternal(this.form.fields.identification)) {
          this.form.routeType.fieldsDisabled.component = true
          this.form.routeType.fieldsDisabled.redirect = true
        } else {
          this.form.routeType.fieldsDisabled.component = false
          this.form.routeType.fieldsDisabled.redirect = false
        }
      }
    }
  },
  created() {
    this.handleList()
    this.icons = svgIconNames
  },
  methods: {
    handleTabSwitch(tab) {
      this.search.fields.type = this.form.fields.type = Number(tab.name)
      this.handleList()
    },
    handleList() {
      const tableType = this.search.fields.type ? 'otherType' : 'routeType'

      this.table[tableType].tableListLoading = true
      getList(this.search.fields).then(response => {
        this.table[tableType].tableListLoading = false
        this.search.searchBtnLoding = false
        this.table[tableType].tableList = response.data.data
      }).catch(e => {
        this.table[tableType].tableListLoading = false
        this.search.searchBtnLoding = false
      })
    },
    handleAdd() {
      const formDataType = this.form.fields.type ? 'otherType' : 'routeType'
      const formType = this.form.fields.type ? 'otherTypeForm' : 'routeTypeForm'
      const treeType = this.form.fields.type ? 'otherTypeTree' : 'routeTypeTree'

      getPermissionTrees(this.form.fields.type).then(response => {
        this.form[formDataType].treeData = response.data.data
        this.$nextTick(() => {
          this.$refs[treeType].setCurrentKey(0)
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
        type: this.search.fields.type,
        parent_id: 0,
        sort: 0,
        status: 1,
        display: 1
      })

      this.form[formDataType].formIsVisible = true
      this.$nextTick(() => {
        this.$refs[formType].clearValidate()
      })
    },
    handleUpdate(scope) {
      const formDataType = this.form.fields.type ? 'otherType' : 'routeType'
      const formType = this.form.fields.type ? 'otherTypeForm' : 'routeTypeForm'
      const treeType = this.form.fields.type ? 'otherTypeTree' : 'routeTypeTree'

      getPermissionTrees(this.form.fields.type).then(response => {
        this.form[formDataType].treeData = response.data.data
        this.$nextTick(() => {
          this.$refs[treeType].setCurrentKey(Number(scope.row.parent_id))
          this.form[formDataType].defaultExpandedNodeId = scope.row.parent_id ? Number(scope.row.parent_id) : 0
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

      this.form[formDataType].formIsVisible = true
      this.$nextTick(() => {
        this.$refs[formType].clearValidate()
      })
    },
    handleDelete(scope) {
      this.$set(scope.row, 'deleteBtnLoading', true)

      deletePermission(scope.row.id).then(response => {
        this.$delete(scope.row, 'deleteBtnLoading')
        if (response.data.code === 'OK') {
          this.$message.success('删除成功')
          this.handleList()
        }
      }).catch(e => {
        this.$delete(scope.row, 'deleteBtnLoading')
      })
    },
    handleSearchList() {
      this.search.searchBtnLoding = true
      this.handleList()
    },
    handleIssueForm() {
      const formDataType = this.search.fields.type ? 'otherType' : 'routeType'
      const formType = this.search.fields.type ? 'otherTypeForm' : 'routeTypeForm'

      this.$refs[formType].validate(valid => {
        if (!valid) return false

        this.form[formDataType].formBtnLoading = true

        if (this.form.fields.id) {
          updatePermission(this.form.fields).then(response => {
            this.handleFormSuccess(response.data.code)
          }).catch(e => {
            this.form[formDataType].formBtnLoading = false
          })
        } else {
          createPermission(this.form.fields).then(response => {
            this.handleFormSuccess(response.data.code)
          }).catch(e => {
            this.form[formDataType].formBtnLoading = false
          })
        }
        this.form[formDataType].formIsVisible = true
      })
    },
    handleFormSuccess(code) {
      const formDataType = this.search.fields.type ? 'otherType' : 'routeType'

      this.form[formDataType].formBtnLoading = false

      if (code === 'OK') {
        this.form[formDataType].formIsVisible = false
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

      editPermission(params).then(response => {
        this.$delete(scope.row, 'editStatusBtnLoading')
        if (response.data.code === 'OK') {
          scope.row.status = Number(!scope.row.status)
          this.$message.success(`成功${willDoAction}`)
        }
      }).catch(e => {
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
</style>
