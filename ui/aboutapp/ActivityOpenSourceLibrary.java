package com.pixelnx.eacademy.ui.aboutapp;

import android.content.Context;
import android.content.res.Configuration;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

import com.pixelnx.eacademy.R;

import com.pixelnx.eacademy.model.modellogin.ModelLogin;
import com.pixelnx.eacademy.ui.base.BaseActivity;
import com.pixelnx.eacademy.utils.AppConsts;
import com.pixelnx.eacademy.utils.sharedpref.SharedPref;
import com.pixelnx.eacademy.utils.widgets.CustomTextExtraBold;
import com.pixelnx.eacademy.utils.widgets.CustomeTextRegular;

import java.util.Locale;

public class ActivityOpenSourceLibrary extends BaseActivity implements View.OnClickListener {
    Context context;
    ImageView ivBack;
    CustomTextExtraBold tvHeader;
    CustomeTextRegular details;
    SharedPref sharedPref;
    ModelLogin modelLogin;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_open_source_library);

        context = getApplicationContext();
        sharedPref = SharedPref.getInstance(context);
        modelLogin = sharedPref.getUser(AppConsts.STUDENT_DATA);

        if(modelLogin.getStudentData().getLanguageName().equalsIgnoreCase("arabic")){
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

        initial();
    }

    void initial() {
        ivBack = findViewById(R.id.ivBack);
        details = findViewById(R.id.details);

        tvHeader = findViewById(R.id.tvHeader);
        tvHeader.setText(""+getResources().getString(R.string.Open_Source_Library));
        ivBack.setOnClickListener(this);
        String library = "Copy Right By Sarah Ben Yahia et Ella Zarmadni" ;

        details.setText(library);

    }

    @Override
    public void onClick(View view) {
        if (view.getId() == R.id.ivBack) {
            onBackPressed();
        }

    }
}