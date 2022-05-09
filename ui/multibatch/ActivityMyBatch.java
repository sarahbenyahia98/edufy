package com.pixelnx.eacademy.ui.multibatch;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.Configuration;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.androidnetworking.AndroidNetworking;
import com.androidnetworking.error.ANError;
import com.androidnetworking.interfaces.ParsedRequestListener;
import com.pixelnx.eacademy.R;
import com.pixelnx.eacademy.model.modellogin.ModelLogin;
import com.pixelnx.eacademy.ui.batch.ActivityBatchDetails;
import com.pixelnx.eacademy.ui.batch.ModelBachDetails;
import com.pixelnx.eacademy.ui.home.ActivityHome;
import com.pixelnx.eacademy.ui.settingdashboard.ActivitySettingDashboard;
import com.pixelnx.eacademy.utils.AppConsts;
import com.pixelnx.eacademy.utils.ProjectUtils;
import com.pixelnx.eacademy.utils.sharedpref.SharedPref;
import com.pixelnx.eacademy.utils.widgets.CustomSmallText;
import com.pixelnx.eacademy.utils.widgets.CustomTextExtraBold;
import com.pixelnx.eacademy.utils.widgets.CustomeTextRegular;
import com.pixelnx.eacademy.utils.widgets.CustomeTextRegularBold;
import com.squareup.picasso.Picasso;

import java.util.ArrayList;
import java.util.Locale;

public class ActivityMyBatch extends AppCompatActivity {
    RecyclerView rlBatchBuyed, rlBatchRecomm;
    Context context;
    ArrayList<ModelBachDetails.batchData> buyBatchList;
    ImageView ivBack, noResult, noResultwo;
    CustomTextExtraBold tvHeader;
    ModelLogin modelLogin;
    SharedPref sharedPref;
    CustomeTextRegularBold headingRecommanded;
   TextView settings, home;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        context = ActivityMyBatch.this;
        sharedPref = SharedPref.getInstance(context);
        modelLogin = sharedPref.getUser(AppConsts.STUDENT_DATA);
        if (!ProjectUtils.checkConnection(context)) {
            Toast.makeText(context, getResources().getString(R.string.NoInternetConnection), Toast.LENGTH_SHORT).show();
        }
        if (modelLogin != null) {
            if (modelLogin.getStudentData() != null) {
                if (modelLogin.getStudentData().getLanguageName().equalsIgnoreCase("arabic")) {
                    //manually set Directions

                    getWindow().getDecorView().setLayoutDirection(View.LAYOUT_DIRECTION_RTL);
                    Configuration configuration = getResources().getConfiguration();
                    configuration.setLayoutDirection(new Locale("fa"));
                    getResources().updateConfiguration(configuration, getResources().getDisplayMetrics());
                    String languageToLoad = "ar"; // your language
                    Locale locale = new Locale(languageToLoad);
                    Locale.setDefault(locale);
                    Configuration config = new Configuration();
                    config.locale = locale;
                    getBaseContext().getResources().updateConfiguration(config,
                            getBaseContext().getResources().getDisplayMetrics());
                }

                if (modelLogin.getStudentData().getLanguageName().equalsIgnoreCase("french")) {
                    String languageToLoad = "fr"; // your language
                    Locale locale = new Locale(languageToLoad);
                    Locale.setDefault(locale);
                    Configuration config = new Configuration();
                    config.locale = locale;
                    getBaseContext().getResources().updateConfiguration(config,
                            getBaseContext().getResources().getDisplayMetrics());


                }
                if (modelLogin.getStudentData().getLanguageName().equalsIgnoreCase("english")) {
                    String languageToLoad = "en"; // you     gbr language
                    Locale locale = new Locale(languageToLoad);
                    Locale.setDefault(locale);
                    Configuration config = new Configuration();
                    config.locale = locale;
                    getBaseContext().getResources().updateConfiguration(config,
                            getBaseContext().getResources().getDisplayMetrics());
                }

            }
        }
        setContentView(R.layout.activity_my_batch);
        initial();


    }

    private void initial() {
        rlBatchBuyed = findViewById(R.id.rlBatchBuyed);
        ivBack = (ImageView) findViewById(R.id.ivBack);
        noResult = (ImageView) findViewById(R.id.noResult);

        ivBack.setVisibility(View.GONE);
        tvHeader = (CustomTextExtraBold) findViewById(R.id.tvHeader);
        tvHeader.setText(getResources().getString(R.string.my_batches));
        ProjectUtils.showProgressDialog(context, false, getResources().getString(R.string.Loading___));
        getBatches();
        settings = findViewById(R.id.settings);
        home = findViewById(R.id.home);
        home.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, ActivityMultiBatchHome.class);
                startActivity(intent);
            }
        });
        settings.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, ActivitySettingDashboard.class);
                startActivity(intent);
            }
        });
    }
    private void exitAppDialog() {

        AlertDialog.Builder builder = new AlertDialog.Builder(context);
        builder.setMessage(getResources().getString(R.string.Are_you_sure_you_want_to_close_this_app))
                .setCancelable(false)
                .setTitle(getResources().getString(R.string.app_name))
                .setPositiveButton(getResources().getString(R.string.Yes), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        Intent intent = new Intent(Intent.ACTION_MAIN);
                        intent.addCategory(Intent.CATEGORY_HOME);
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                        startActivity(intent);
                    }
                })
                .setNegativeButton(getResources().getString(R.string.No), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });


        AlertDialog alertDialog = builder.create();


        alertDialog.setOnShowListener(new DialogInterface.OnShowListener() {
            @Override
            public void onShow(DialogInterface arg0) {
                if (modelLogin.getStudentData().getLanguageName().equalsIgnoreCase("arabic")) {
                    alertDialog.findViewById(android.R.id.message).setTextDirection(View.TEXT_DIRECTION_RTL);
                }

                alertDialog.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getResources().getColor(R.color.colorPrimaryDark));
                alertDialog.getButton(AlertDialog.BUTTON_NEGATIVE).setTextColor(getResources().getColor(R.color.colorPrimaryDark));

            }
        });
        alertDialog.show();


    }

    @Override
    public void onBackPressed() {
       exitAppDialog();
    }

    void getBatches() {


        AndroidNetworking.post(AppConsts.BASE_URL + AppConsts.API_GET_BATCH_FEE)
                .addBodyParameter(AppConsts.STUDENT_ID, "" + modelLogin.getStudentData().getStudentId())
                .build(
                ).getAsObject(ModelBachDetails.class, new ParsedRequestListener<ModelBachDetails>() {
            @Override
            public void onResponse(ModelBachDetails response) {
                ProjectUtils.pauseProgressDialog();
                buyBatchList = new ArrayList<>();
                if (response.getStatus().equalsIgnoreCase("true")) {
                    if (response.getYourBatch() != null) {
                        buyBatchList = response.getYourBatch();
                        if(buyBatchList.size() > 0){
                        noResult.setVisibility(View.GONE);
                        }else{
                            noResult.setVisibility(View.VISIBLE);
                        }
                    } else {
                        noResult.setVisibility(View.VISIBLE);
                    }
                    rlBatchBuyed.setLayoutManager(new LinearLayoutManager(context));
                    AdapterCustom adapterCustom = new AdapterCustom(context, buyBatchList, "yourbatch");
                    rlBatchBuyed.setAdapter(adapterCustom);




                } else {
                    buyBatchList = new ArrayList<>();
                    noResult.setVisibility(View.VISIBLE);
                    rlBatchBuyed.setLayoutManager(new GridLayoutManager(context, 2));
                    AdapterCustom adapterCustom = new AdapterCustom(context, buyBatchList, "yourbatch");
                    rlBatchBuyed.setAdapter(adapterCustom);
                }
            }

            @Override
            public void onError(ANError anError) {
                Toast.makeText(context, getResources().getString(R.string.Try_again), Toast.LENGTH_SHORT).show();
                ProjectUtils.pauseProgressDialog();

            }
        });


    }

    class AdapterCustom extends RecyclerView.Adapter<AdapterCustom.Myholder> {
        ArrayList<ModelBachDetails.batchData> listBatch;
        String tag = "";
        View view;

        AdapterCustom(Context context, ArrayList<ModelBachDetails.batchData> list, String tag) {
            this.listBatch = list;
            this.tag = tag;
        }

        @NonNull
        @Override
        public AdapterCustom.Myholder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            view = LayoutInflater.from(context).inflate(R.layout.multibatch_list, parent, false);
            return new AdapterCustom.Myholder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull AdapterCustom.Myholder holder, int position) {



                holder.layoutBatch.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        if (ProjectUtils.checkConnection(context)) {
                            if(listBatch.get(position).getStatus().equalsIgnoreCase("1")){
                            changeBatch("" + listBatch.get(position).getId());}else{
                                Toast.makeText(context,"Your batch is inactive!", Toast.LENGTH_SHORT).show();
                            }
                        } else {
                            Toast.makeText(context, getResources().getString(R.string.NoInternetConnection), Toast.LENGTH_SHORT).show();
                        }
                    }
                });


                holder.batchName.setText("" + listBatch.get(position).getBatchName());

if(listBatch.get(position).getStatus().equalsIgnoreCase("1")){
 holder.inactive.setVisibility(View.GONE);
 holder.ivArrow.setVisibility(View.VISIBLE);

}else{

    holder.inactive.setVisibility(View.VISIBLE);
    holder.ivArrow.setVisibility(View.GONE);
}
        }

        @Override
        public int getItemCount() {
            return listBatch.size();
        }

        class Myholder extends RecyclerView.ViewHolder {
            CustomSmallText batchName;
            RelativeLayout layoutBatch;
            TextView inactive;
            ImageView ivArrow;

            public Myholder(@NonNull View itemView) {
                super(itemView);

                batchName = itemView.findViewById(R.id.batchName);
                layoutBatch = itemView.findViewById(R.id.layoutBatch);
                inactive = itemView.findViewById(R.id.inactive);
                ivArrow = itemView.findViewById(R.id.ivArrow);

            }
        }
    }

    void changeBatch(String s) {

        AndroidNetworking.post(AppConsts.BASE_URL + AppConsts.API_CHANGE_BATCH)
                .addBodyParameter(AppConsts.STUDENT_ID, "" + modelLogin.getStudentData().getStudentId())
                .addBodyParameter(AppConsts.BATCH_ID, "" + s)
                .build().getAsObject(ModelLogin.class, new ParsedRequestListener<ModelLogin>() {
            @Override
            public void onResponse(ModelLogin response) {
                if (AppConsts.TRUE.equals(response.getStatus())) {
                    sharedPref.setUser(AppConsts.STUDENT_DATA, response);
                    startActivity(new Intent(context, ActivityHome.class));

                } else {
                    Toast.makeText(context, "" + response.getMsg(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onError(ANError anError) {

                Toast.makeText(context, "" + context.getResources().getString(R.string.Try_again_server_issue), Toast.LENGTH_SHORT).show();

            }
        });


    }
}